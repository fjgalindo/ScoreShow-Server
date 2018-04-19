<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\FollowUsr;
use api\modules\v1\models\LoginForm;
use api\modules\v1\models\User;
use api\modules\v1\models\ServerResponse;
use Yii;
use yii\filters\auth\HttpBearerAuth;

class UserController extends \yii\rest\ActiveController
{

    public $enableCsrfValidation = false;
    public $modelClass = 'api\modules\v1\models\User';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'optional' => ['auth', 'create'],
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'auth' => ['POST'],
                'create' => ['POST'],
                'view' => ['GET'],
                'update' => ['PUT', 'POST'],
                'my-comments' => ['GET'],
                'my-stats' => ['GET'],
                'follow-user' => ['POST'],
                'unfollow-user' => ['POST'],
            ],
        ];
        /*
        $behaviors['corsFilter'] = [
        'class' => Cors::className(),
        'cors' => [
        'Origin' => ['*'],
        'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
        'Access-Control-Request-Headers' => ['*'],
        'Access-Control-Allow-Credentials' => true,
        ],
        ];
         */
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['delete'], $actions['create'], $actions['update'], $actions['view']);
        return $actions;
    }

    public function actionAuth()
    {
        if (!Yii::$app->user->isGuest) {
            return new ServerResponse(12);
        }

        $model = new LoginForm();

        $model->attributes = Yii::$app->request->bodyParams;
        if ($user = $model->login()) {
            return ['token' => $user->token, 'id' => $user->id, 'name' => $user->name];
        } else {
            return $model;
        }

        /*
    $response = [];
    $params = Yii::$app->request->bodyParams; // Send data from xxx-form-url-encoded

    if (isset($params['username']) && isset($params['password'])) {
    $username = $params['username'];
    $password = $params['password'];

    if ($u = User::findOne(['username' => $username])) {
    if ($u->password == md5($password)) { //o crypt, según esté en la BD
    $response = ['token' => $u->token, 'id' => $u->id, 'name' => $u->name];
    }
    } else {
    $response = ['error' => 'Usuario incorrecto. ' . $username];
    }
    // Return error message no auth request
    } else {
    $response['message'] = "Debes especificar un nombre de usuario y una contraseña";
    }
    return $response;
     */
    }

    public function actionView($id)
    {
        $response = [];

        if ($model = User::findOne(['id' => $id])) {
            $model->scenario = User::SCENARIO_VIEW;

            if ($id === Yii::$app->user->identity->id) {
                return $this->actionProfile();
            }

            //$model->activity = $model->userActivity;

            $response['profile'] = $model;
            $response['activity'] = $model->userActivity;

        } else {
            return new ServerResponse(34);
        }
        return $response;
    }

    public function actionMyComments()
    {
        $model = Yii::$app->user->identity;
        $comments = $model->comments;
        return $comments;
    }

    public function actionStats()
    {
        return "OK";
    }

    public function actionCreate()
    {
        if (!Yii::$app->user->isGuest) {
            return new ServerResponse(12);
        }

        $user = new User();
        $user->scenario = $user::SCENARIO_CREATE;

        $params = Yii::$app->request->bodyParams; // Get data from xxx-form-url-encoded
        $user->attributes = $params;

        $user->auth_key = Yii::$app->security->generateRandomString(32);
        $user->tmdb_gtoken = Yii::$app->TMDb->generateGuestSessionId();
        $user->status = 0;
        $user->password = Yii::$app->security->generatePasswordHash($user->password);

        if ($user->validate()) {
            $user->save(false);
        } else {
            return new ServerResponse(5, $user->errors);
        }

        return $user;
    }

    public function actionUpdate()
    {
        $user = Yii::$app->user->identity;
        $user->scenario = $user::SCENARIO_UPDATE;

        $params = Yii::$app->request->bodyParams; // Get data from xxx-form-url-encoded
        $user->attributes = $params;

        // If user wants to change password
        if (isset($params['password']) || isset($params['repeat_password'])) {
            if (!$user->validateModifiedPassword()) { // If passwords are not valid
                return new ServerResponse(5, $user->errors);
            }
            
            $user->password = Yii::$app->security->generatePasswordHash($user->password);
        }

        $user->updated_at = date("Y-m-d H-i-s");
        if ($user->validate()) {
            $user->save(false);
        } else {
            return new ServerResponse(5, $user->errors);
        }

        return $user;
    }

    public function actionProfile()
    {
        $response = [];
        $user = Yii::$app->user->identity;
        $response['profile'] = $user;
        $response['activity'] = $user->getUserActivity();
        return $response;
    }

    public function actionFollowUser($id)
    {
        
        $user = Yii::$app->user->identity;
        if ($followed = User::findOne(['id' => $id])) {
            if (!$model = FollowUsr::findOne(['follower' => $user->id, 'followed' => $id])) {
                $followusr = new FollowUsr();
                $followusr->follower = $user->id;
                $followusr->followed = $followed->id;

                if (!$followusr->save()) {
                    return new ServerResponse(10);
                }
            }
        } else {
            //$response['message'] = "El usuario indicado no existe";
            return new ServerResponse(34);
        }

        return new ServerResponse(1);   // This means user is following the specified user (no changes needed)

    }

    public function actionUnfollowUser($id)
    {
        $user = Yii::$app->user->identity;
        if ($model = FollowUsr::findOne(['follower' => $user->id, 'followed' => $id])) {
            if (!$model->delete()) {
                return new ServerResponse(10);
            }
        }

        return new ServerResponse(1);   // This means user is not following the user actually or unfollow successfully
    }

}
