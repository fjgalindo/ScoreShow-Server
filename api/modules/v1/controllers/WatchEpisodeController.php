<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\FollowTitle;
use api\modules\v1\models\ServerResponse;
use api\modules\v1\models\WatchEpisode;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;

class WatchEpisodeController extends \yii\rest\ActiveController
{
    public $enableCsrfValidation = false;
    public $modelClass = 'api\modules\v1\models\WatchEpisode';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['http: //localhost:8100', '*'],
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
                'watch' => ['POST', 'OPTIONS'],
                'unwatch' => ['POST', 'OPTIONS'],
                'score' => ['POST', 'OPTIONS'],
            ],
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        //Eliminamos acciones de crear y eliminar apuntes. Eliminamos update para personalizarla
        unset($actions['delete'], $actions['create'], $actions['view'], $actions['update'], $actions['index']);
        return $actions;
    }

    public function actionWatch($id, $season, $ep)
    {
        $uid = Yii::$app->user->identity->id;

        if (!$model = FollowTitle::findOne(
            [
                'title' => $id, 'user' => $uid,
            ])
        ) {
            Yii::$app->controller->module->runAction(
                'follow-title/follow', ['title_id' => $id]
            );
        }

        if (!$model = WatchEpisode::findOne(
            [
                'user' => $uid,
                'tvshow' => $id, 'season_num' => $season, 'episode_num' => $ep,
            ])
        ) {
            $model = new WatchEpisode();
            $model->user = Yii::$app->user->identity->id;
            $model->tvshow = $id;
            $model->season_num = $season;
            $model->episode_num = $ep;
            $model->date = date("Y-m-d H-i-s");

            if (!$model->save()) {
                return new ServerResponse(10, $model->errors);
            }
        }

        return new ServerResponse(1);
    }

    public function actionUnwatch($id, $season, $ep)
    {
        $uid = Yii::$app->user->identity->id;
        if ($model = WatchEpisode::findOne(
            [
                'user' => $uid,
                'tvshow' => $id, 'season_num' => $season, 'episode_num' => $ep,
            ])) {

            if (!$model->delete()) {
                return new ServerResponse(10);
            }
        }

        return new ServerResponse(1);
    }

    public function actionScore($id, $season, $ep, $score)
    {
        $uid = Yii::$app->user->identity->id;

        if (!$model = WatchEpisode::findOne(['user' => $uid,
            'tvshow' => $id, 'season_num' => $season, 'episode_num' => $ep,
        ])) {
            return new ServerResponse(16);
        }

        $model->score = round($score * 2) / 2; // Round score to nearset multiple of 0.5

        $gsesid = Yii::$app->user->identity->tmdb_gtoken;
        $id_tmdb = $model->episodeModel->tvshowModel->title->id_tmdb;

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

        if (!Yii::$app->TMDb->rateEpisode($id_tmdb, $model->season_num, $model->episode_num, $gsesid, $score)
            || !$model->save()) {
            return new ServerResponse(10);
        }
        return new ServerResponse(1);
    }
}
