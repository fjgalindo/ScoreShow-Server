<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\FollowTitle;
use api\modules\v1\models\ServerResponse;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;

class FollowTitleController extends \yii\rest\ActiveController
{
    public $modelClass = 'api\modules\v1\models\FollowTitle';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
        ];
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'follow' => ['POST'],
                'unfollow' => ['POST'],
            ],
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['view']);
        return $actions;
    }
    /*
    public function actionFollow($title_id)
    {
    $response = [];
    $user = Yii::$app->user->identity->id;

    // Set up new model
    $model = new FollowTitle();
    $model->user = $user;
    $model->title = $title_id;
    $model->date = date("Y-m-d");
    try {
    if ($model->save()) { // First time viewed => Insert on DB
    $response['message'] = "Success";
    $response['error'] = 0;
    } else if ($model = FollowTitle::findOne(['user' => $user, 'title' => $title_id])) {
    $model->date = date("Y-m-d"); // Set new date
    if($model->save()){
    $response['message'] = "Updated entry";
    $response['error'] = 0;
    }
    } else { // Problems appeared
    return $model->errors;
    }
    } catch (yii\db\Exception $e) { // Big problems appeared
    $response['message'] = "No se han podido guardar los cambios.";
    $response['db_message'] = $e->getName();
    $response['error'] = "27";
    }

    return $response;
    }
     */
    public function actionFollow($title_id)
    {
        $response = [];
        $user = Yii::$app->user->identity;

        // If user is not actually following the specified title
        if (!$model = FollowTitle::findOne(['user' => $user, 'title' => $title_id])) {

            $model = new FollowTitle();
            $model->user = $user->id;
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
