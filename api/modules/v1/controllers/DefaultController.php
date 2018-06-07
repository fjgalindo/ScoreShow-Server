<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\ServerResponse;
use api\modules\v1\models\User;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;

/**
 * Default controller for the `v1` module
 */
class DefaultController extends \yii\rest\ActiveController
{

    public $modelClass = '';

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
            'except' => ['image-tmdb', 'image', 'say-hello'],
        ];
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'search-tmdb' => ['GET', 'OPTIONS'],
                'image-tmdb' => ['GET', 'OPTIONS'],
                'image' => ['GET', 'OPTIONS'],
            ],
        ];

        return $behaviors;
    }

    public function actionSayHello()
    {
        return "Finally, hello from the ScoreShow API!";
    }

    public function actionSearchTmdb($query, $page = 1)
    {
        if (!$query) {
            return new ServerResponse(5, ['query' => 'Query text must be set']);
        }

        return Yii::$app->TMDb->search($query, "multi", $page);
    }

    public function actionImageTmdb($img = "")
    {
        return Yii::$app->TMDb->getImageUrl($img);
    }

    public function actionImage($filename)
    {
        $path = User::IMG_DIR . $filename;
        if (file_exists($path)) {
            return Yii::$app->response->sendFile($path);
        }
    }
}
