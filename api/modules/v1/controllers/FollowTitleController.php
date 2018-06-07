<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\FollowTitle;
use api\modules\v1\models\ServerResponse;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;

class FollowTitleController extends \yii\rest\ActiveController
{
    public $enableCsrfValidation = false;
    public $modelClass = 'api\modules\v1\models\FollowTitle';

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
                'follow' => ['POST', 'OPTIONS'],
                'unfollow' => ['POST', 'OPTIONS'],
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

    public function actionFollow($title_id)
    {
        $uid = Yii::$app->user->identity->id;

        // If user is not actually following the specified title
        if (!$model = FollowTitle::findOne(['user' => $uid, 'title' => $title_id])) {
            $model = new FollowTitle();
            $model->user = $uid;
            $model->title = $title_id;
            $model->date = date("Y-m-d");

            if (!$model->save()) {
                return new ServerResponse(10, $model->errors);
            }
        }

        return new ServerResponse(1);
    }

    public function actionUnfollow($title_id)
    {
        $response = [];
        $uid = Yii::$app->user->identity->id;

        if ($model = FollowTitle::findOne(['user' => $uid, 'title' => $title_id])) {
            if (!$model->delete()) {
                return new ServerResponse(10);
            }
        }

        return new ServerResponse(1);
    }

}
