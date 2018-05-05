<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;

class TitleController extends ActiveController
{
    public $enableCsrfValidation = false;
    public $modelClass = 'api\modules\v1\models\Title';

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
                'premieres' => ['GET', 'OPTIONS'],
                '*' => ['OPTIONS'],
            ],
        ];
        return $behaviors;
    }

    public function actionPremieres()
    {
        $tvshows_premieres = Yii::$app->controller->module->runAction(
            'episode/premieres'
        );

        $movies_premieres = Yii::$app->controller->module->runAction(
            'movie/premieres'
        );

        $premieres = array_merge_recursive($tvshows_premieres, $movies_premieres);
        return $premieres;

    }

}
