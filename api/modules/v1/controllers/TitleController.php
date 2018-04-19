<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Title;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;

class TitleController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Title';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
        ];
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'checkCacheExpiration' => [''],
            ],
        ];
        return $behaviors;
    }

    

    

}
