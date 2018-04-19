<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\ServerResponse;
use api\modules\v1\models\WatchEpisode;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;

class WatchEpisodeController extends \yii\rest\ActiveController
{
    public $modelClass = 'api\modules\v1\models\WatchMovie';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
        ];
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index' => ['GET'],
                'add' => ['POST'],
                'view' => ['GET'],
                'watch' => ['POST'],
                'unwatch' => ['POST'],
                'score' => ['POST'],
            ],
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        //Eliminamos acciones de crear y eliminar apuntes. Eliminamos update para personalizarla
        unset($actions['delete'], $actions['create'], $actions['view']);
        return $actions;
    }

    /*
    public function actionIndex()
    {
    return $this->render('index');
    }*/

    public function actionWatch($id, $season, $ep)
    {
        $uid = Yii::$app->user->identity->id;
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
/*
    public function actionWatchSeason($id, $season)
    {
        $response = [];
        $uid = Yii::$app->user->identity->id;

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
        }
        $model->date = date("Y-m-d H-i-s");

        if (!$model->save()) {
            return new ServerResponse(10);
        }
        return new ServerResponse(1);
    }

    public function actionUnwatchSeason($id, $season)
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
*/
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
