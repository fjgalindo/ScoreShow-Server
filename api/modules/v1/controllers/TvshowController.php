<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\ServerResponse;
use api\modules\v1\models\Title;
use api\modules\v1\models\Tvshow;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;

class TvshowController extends ActiveController
{
    public $enableCsrfValidation = false;
    public $modelClass = 'api\modules\v1\models\Tvshow';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
            ],
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'except' => ['options'],
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'get' => ['GET', 'OPTIONS'],
                'view-model' => ['GET', 'OPTIONS'],
                'last-comments' => ['GET', 'OPTIONS'],
                'platforms' => ['GET', 'OPTIONS'],
                'follow' => ['POST', 'OPTIONS'],
                'unfollow' => ['POST', 'OPTIONS'],
                'view-comments' => ['GET', 'OPTIONS'],
                'comment' => ['POST', 'OPTIONS'],
                'list-season' => ['GET', 'OPTIONS'],
                'watch-season' => ['POST', 'OPTIONS'],
                'unwatch-season' => ['POST', 'OPTIONS'],
                'last-comments' => ['GET', 'OPTIONS'],

                'recommendations' => ['GET', 'OPTIONS'],
                'popular' => ['GET', 'OPTIONS'],
                'top-rated' => ['GET', 'OPTIONS'],
                '*' => ['OPTIONS'],
            ],
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        //Eliminamos acciones de crear y eliminar apuntes. Eliminamos update para personalizarla
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['view']);
        //$actions['listseason']['prepareDataProvider'] = [$this, 'listseason'];
        return $actions;
    }

    /**
     * !!!!!!! ELEMENTAL FUNCTION !!!!!!!!!!
     * This action is called when...
     * - Frontend: Client requests to view movie from tmdb search results.
     * - Backend: Admin press add button from tmdb search results.
     */
    public function addTVShow($id_tmdb)
    {
        // Check if this id exists on TMDb as a tvshow
        $tmdb_data = Yii::$app->TMDb->getTitleData($id_tmdb, 'tv', ['credits', 'images']);

        if (!isset($tmdb_data['id'])) {
            return false;
        }

        $title = new Title();
        $title->id_tmdb = $tmdb_data['id'];
        $title->cache = json_encode($tmdb_data);
        $title->last_update = date("Y-m-d H-i-s");

        /* POSIBLE TRANSACCION AQUI */
        if ($title->save()) {
            $tvshow = new Tvshow();
            $tvshow->id = $title->id;
            if ($tvshow->save()) {
                return $tvshow;
            }
        }

        return false;
    }

    /**
     * Called on click a search result from tmdb search component
     */
    public function actionGet($id_tmdb)
    {
        if (!$model = Tvshow::getTvShowByTMDbId($id_tmdb)) {
            $model = $this->addTVShow($id_tmdb);
        }

        if ($model) {
            return $this->actionViewModel($model->id);
        } else {
            return new ServerResponse(34);
        }

    }

    public function actionViewModel($id)
    {
        if (!$tvshow = Tvshow::findOne($id)) {
            return new ServerResponse(34);
        }

        $model = $tvshow->title;
        if ($model->needsUpdate()) {
            if (!$model = $this->updateCache($model->id)) {
                return new ServerResponse(10);
            }
        }

        $response = $model->cache;
        $response['_id'] = $model->id;
        $response['following'] = $model->isFollowedByUser();

        return $response;
    }

    public function actionLastComments($id)
    {
        if (!$tvshow = Tvshow::findOne($id)) {
            return new ServerResponse(34);
        }

        return $tvshow->title->lastComments;
    }

    public function actionPlatforms($id)
    {
        if (!$tvshow = Tvshow::findOne($id)) {
            return new ServerResponse(34);
        }

        return $tvshow->title->platformLinks;
    }

    public function actionFollow($id)
    {
        if (!$tvshow = Tvshow::findOne($id)) { // If movie exists
            return new ServerResponse(34);
        }

        return Yii::$app->controller->module->runAction(
            'follow-title/follow', ['title_id' => $tvshow->id]
        );
    }

    public function actionUnfollow($id)
    {
        if (!$tvshow = Tvshow::findOne($id)) { // If movie exists
            return new ServerResponse(34);
        }

        return Yii::$app->controller->module->runAction(
            'follow-title/unfollow', ['title_id' => $tvshow->id]
        );

    }

    public function actionViewComments($id)
    {
        if (!$model = Tvshow::findOne(['id' => $id])) {
            return new ServerResponse(34);
        }
        return $model->title->comments;
    }

    public function actionComment($id)
    {
        if (!$model = Tvshow::findOne(['id' => $id])) {
            return new ServerResponse('34');
        }

        return Yii::$app->controller->module->runAction(
            'comment/comment', ['title' => $id]
        );
    }

    public function updateCache($title_id)
    {
        $title = Title::findOne(['id' => $title_id]);
        $title->cache = $title->tvshow->getTMDbData();
        $title->last_update = date("Y-m-d H-i-s");

        if ($title->save()) {
            return $title;
        }

        return false;
    }

    public function actionRecommendations()
    {
        if ($arr = Yii::$app->user->identity->followedTvshows) {
            $key = array_rand($arr, 1);
            $id_tmdb = Yii::$app->user->identity->followedTvshows[$key]['id_tmdb'];
            $res = Yii::$app->TMDb->getRecommendations($id_tmdb, 'tv');
            if ($res['results']) {
                return $res;
            }
        }
        return false;
    }

    public function actionPopular()
    {
        return Yii::$app->TMDb->getPopular('tv');
    }

    public function actionTopRated()
    {
        return Yii::$app->TMDb->getTopRated('tv');
    }
}
