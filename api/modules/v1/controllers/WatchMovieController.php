<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Movie;
use api\modules\v1\models\ServerResponse;
use api\modules\v1\models\User;
use api\modules\v1\models\WatchMovie;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;

class WatchMovieController extends ActiveController
{
    public $enableCsrfValidation = false;
    public $modelClass = 'api\modules\v1\models\WatchMovie';

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
                'watch'=> ['POST', 'OPTIONS'],
                'unwatch'=> ['POST', 'OPTIONS'],
                //'update'=> ['POST', 'OPTIONS'],
                'score'=> ['POST', 'OPTIONS'],
            ],
        ];

        return $behaviors;
    }
    
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['view'], $actions['index']);
        return $actions;
    }

    public function actionWatch($id)
    {
        $response = [];

        $model = new WatchMovie();
        $model->user = Yii::$app->user->identity->id;
        $model->movie = $id;
        $model->date = date("Y-m-d H-i-s");
        try {
            if ($model->save()
                // || $this->actionUpdate($id)
            ) {
                $response['message'] = "Success";
                $response['error'] = 0;

            } else if ($this->actionUpdate($id)) {
                $response['message'] = "Updated entry";
                $response['error'] = 0;

            } else {
                throw new Exception("Unknown error");
            }
        } catch (yii\db\Exception $e) {
            $response['message'] = "No se han podido guardar los cambios.";
            $response['db_message'] = $e->getName();
            $response['error'] = "27";
        }
        return $response;
    }

    public function actionUnwatch($id)
    {
        $response = [];

        if ($model = WatchMovie::findOne(['user' => Yii::$app->user->identity->id, 'movie' => $id])) {
            try {
                if ($model->delete()) {
                    $response['message'] = "Success";
                    $response['error'] = 0;
                } else {
                    throw new Exception("Unknown error");
                }
            } catch (yii\db\Exception $e) {
                $response['message'] = "No se han podido guardar los cambios.";
                $response['db_message'] = $e->getName();
                $response['error'] = "28";
            }
        } else {
            // -------------------------------------> This can be ignored directly
            $response['message'] = "Error: No has marcado esta película como vista anteriormente";
            $response['error'] = "33";
        }

        return $response;
    }
/*
    public function actionUpdate($id)
    {
        $success = false;
        if ($model = WatchMovie::findOne(['user' => Yii::$app->user->identity->id, 'movie' => $id])) {
            $model->date = date("Y-m-d H-i-s");
            if ($model->save()) {
                $success = true;
            }
        }
        return $success;
    }
*/
    public function actionScore($id, $score)
    {
        $uid = Yii::$app->user->identity->id;

        if (!$model = WatchMovie::findOne(['user' => $uid, 'movie' => $id])) {
            return new ServerResponse(16);
        }

        $model->score = round($score * 2) / 2; // Round score to nearset multiple of 0.5

        $gsesid = Yii::$app->user->identity->tmdb_gtoken;
        $id_tmdb = $model->movieModel->title->id_tmdb;

        if (!Yii::$app->TMDb->checkGuestSessionId($gsesid)) {
            //$user = User::findOne(['tmdb_gtoken' => $gsesid]);
            $user = Yii::$app->user->identity;
            if ($gsesid = Yii::$app->TMDb->generateGuestSessionId()) {
                $user->tmdb_gtoken = $gsesid;
                $user->save();
            } else {
                return new ServerResponse(10);
            }
        }

        if (!Yii::$app->TMDb->rateMovie($id_tmdb, $gsesid, $score) || !$model->validate()) {
            return new ServerResponse(10);
        }

        $model->save(false);

        return new ServerResponse(1);
    }

}
