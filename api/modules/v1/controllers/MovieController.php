<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Movie;
use api\modules\v1\models\ServerResponse;
use api\modules\v1\models\Title;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;

class MovieController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Movie';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
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
                'follow' => ['POST', 'OPTIONS'],
                'unfollow' => ['POST', 'OPTIONS'],
                'watch' => ['POST', 'OPTIONS'],
                'unwatch' => ['POST', 'OPTIONS'],
                'score' => ['POST', 'OPTIONS'],
                'view-comments' => ['GET', 'OPTIONS'],
                'last-comments' => ['GET', 'OPTIONS'],
                'platforms' => ['GET', 'OPTIONS'],
                'to-watch' => ['GET', 'OPTIONS'],
                'comment' => ['POST', 'OPTIONS'],
            ],
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        //Eliminamos acciones de crear y eliminar apuntes. Eliminamos update para personalizarla
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['view']);
        return $actions;
    }

    /**
     * !!!!!!! ELEMENTAL FUNCTION !!!!!!!!!!
     * This action is called when...
     * - Frontend: Client requests to view movie from tmdb search results.
     * - Backend: Admin press add button from tmdb search results.
     */
    public function addMovie($id_tmdb)
    {
        // Check if this id exists on TMDb as a tvshow
        $tmdb_data = Yii::$app->TMDb->getTitleData($id_tmdb, 'movie', ['credits', 'images']);

        if (!isset($tmdb_data['id'])) {
            return false;
        }

        $title = new Title();
        $title->id_tmdb = $tmdb_data['id'];
        $title->cache = json_encode($tmdb_data);
        $title->last_update = date("Y-m-d H-i-s");

        /* POSIBLE TRANSACCION AQUI */
        if ($title->save()) {
            $movie = new Movie();
            $movie->id = $title->id;
            if ($movie->save()) {
                return $movie;
            }
        }

        return false;
    }

    /**
     * Called on click a search result from tmdb search component
     */
    public function actionGet($id_tmdb)
    {

        if (!$model = Movie::getByTMDbId($id_tmdb)) {
            $model = $this->addMovie($id_tmdb);
        }

        if (!$model) {
            return new ServerResponse(34);
        }

        return Yii::$app->controller->module->runAction(
            'movie/view', ['id' => $model->id]
        );

    }

    public function actionViewModel($id)
    {
        if (!$movie = Movie::findOne($id)) {
            return new ServerResponse(34);
        }

        $model = $movie->title;
        if ($model->needsUpdate() && $model->cache) {
            if (!$model = $this->updateCache($model->id)) {
                return new ServerResponse(10);
            }
        }

        $response = json_decode($model->cache, true);
        //$response['last_comments'] = $model->getLastComments();
        $response['following'] = $model->isFollowedByUser();
        //$response['platforms'] = $model->platformLinks;

        $response['watched'] = $movie->isWatched();
        $response['myscore'] = $movie->myScore;

        return $response;
    }

    public function actionLastcomments($id)
    {
        if (!$movie = Movie::findOne($id)) {
            return new ServerResponse(34);
        }

        return $movie->title->lastComments;
    }

    public function actionPlatforms($id)
    {
        if (!$movie = Movie::findOne($id)) {
            return new ServerResponse(34);
        }

        return $movie->title->platformLinks;
    }

    public function actionFollow($id)
    {
        if (!$movie = Movie::findOne($id)) { // If movie exists
            return new ServerResponse(34);
        }

        return Yii::$app->controller->module->runAction(
            'follow-title/follow', ['title_id' => $movie->id]
        );

    }

    public function actionUnfollow($id)
    {
        if (!$movie = Movie::findOne($id)) { // If movie exists
            return new ServerResponse(34);
        }

        return Yii::$app->controller->module->runAction(
            'follow-title/unfollow', ['title_id' => $movie->id]
        );

    }

    public function actionWatch($id)
    {

        if (!$movie = Movie::findOne($id)) {
            return new ServerResponse(34);
        }

        if (!$movie->isReleased()) {
            return new ServerResponse(14);
        }

        return Yii::$app->controller->module->runAction(
            'watch-movie/watch', ['id' => $movie->id]
        );
    }

    public function actionUnwatch($id)
    {

        if (!$movie = Movie::findOne($id)) { // Check if movie exists
            return new ServerResponse(34);
        }

        return Yii::$app->controller->module->runAction(
            'watch-movie/unwatch', ['id' => $movie->id]
        );
    }

    public function actionViewComments($id)
    {
        if (!$model = Movie::findOne(['id' => $id])) {
            return new ServerResponse(34);
        }

        return $model->title->comments;

    }

    public function actionComment($id)
    {
        if (!$model = Movie::findOne(['id' => $id])) {
            return new ServerResponse('34');
        }

        return Yii::$app->controller->module->runAction(
            'comment/comment', ['title' => $id]
        );
    }

    public function actionScore($id)
    {
        $score = isset($_POST['score']) ? $_POST['score'] : null;

        if ($score === null) {
            return new ServerResponse(5, ['score' => 'Field score is required']);
        } else if (!is_numeric($score)) {
            return new ServerResponse(5, ['score' => 'Field score should be a number']);
        } else if ($score < 0.5 || $score > 10) {
            return new ServerResponse(18);
        }

        if (!$movie->isReleased()) {
            return new ServerResponse(14);
        }

        return Yii::$app->controller->module->runAction(
            'watch-movie/score', ['id' => $movie->id, 'score' => $score]
        );

    }

    public function actionToWatch()
    {

    }

    public function updateCache($title_id)
    {
        $title = Title::findOne(['id' => $title_id]);

        //$title->cache = Yii::$app->TMDb->getTitleData($title->id_tmdb, 'tv', ['credits', 'images'], false);
        $title->cache = json_encode($title->movie->getTMDbData());

        $title->last_update = date("Y-m-d H-i-s");

        if ($title->save()) {
            return $title;
        }

        return false;
    }
}
