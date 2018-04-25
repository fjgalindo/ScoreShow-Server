<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\db\Exception;
use api\modules\v1\models\ServerResponse;
use yii\filters\auth\HttpBearerAuth;


/**
 * Default controller for the `v1` module
 */
class DefaultController extends \yii\rest\ActiveController
{
    
    public $modelClass='';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
        ];
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'search-tmdb' => ['GET'],
            ],
        ];

        return $behaviors;
    }

    public function actionSearchTmdb($query, $page = 1){
        if(!$query){
            return new ServerResponse(5, ['query'=>'Query text must be set']);
        }

        $results = Yii::$app->TMDb->search($query, "multi", $page);
        return $results;
    }
    
}
