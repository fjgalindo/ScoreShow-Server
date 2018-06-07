<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\FollowUsr;
use api\modules\v1\models\LoginForm;
use api\modules\v1\models\ServerResponse;
use api\modules\v1\models\User;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\web\UploadedFile;

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
            'optional' => ['auth', 'register'],
            'except' => ['options'],
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'auth' => ['POST', 'OPTIONS'],
                'register' => ['POST', 'OPTIONS'],
                'view-model' => ['GET', 'OPTIONS'],
                'profile' => ['GET', 'OPTIONS'],
                'activity' => ['GET', 'OPTIONS'],
                'update-model' => ['PUT', 'POST', 'OPTIONS'],
                'upload-image' => ['PUT', 'POST', 'OPTIONS'],
                'my' => ['GET', 'OPTIONS'],
                'my-comments' => ['GET', 'OPTIONS'],
                'my-stats' => ['GET', 'OPTIONS'],
                'follow-user' => ['POST', 'OPTIONS'],
                'unfollow-user' => ['POST', 'OPTIONS'],
                'followeds' => ['GET', 'OPTIONS'],
                'followeds-activity' => ['GET', 'OPTIONS'],
                'find-by-name' => ['GET', 'OPTIONS'],
                '*' => ['OPTIONS'],
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

    public function actionAuth()
    {
        if (!Yii::$app->user->isGuest) {
            return new ServerResponse(12);
        }

        $model = new LoginForm();

        $model->attributes = Yii::$app->request->bodyParams;
        if ($user = $model->login()) {
            return ['auth_key' => $user->auth_key];
        } else {
            return $model;
        }
    }

    public function actionViewModel($id)
    {
        //$response = [];
        if ($model = User::findOne(['id' => $id])) {
            $model->scenario = User::SCENARIO_VIEW;

            if ($id === Yii::$app->user->identity->id) {
                return $this->actionProfile();
            }

            //$model->activity = $model->userActivity;

            //$response['profile'] = $model;
            //$response['activity'] = $model->userActivity;
            // #########################
            // ==============> $response['following'] = $model->isFollowedByUser();
            // #########################
            return $model;
        } else {
            return new ServerResponse(34);
        }
        //return $response;
    }

    public function actionActivity($id)
    {
        if ($model = User::findOne(['id' => $id])) {
            return $model->userActivity;
        } else {
            return new ServerResponse(34);
        }
    }

    public function actionMyComments()
    {
        $model = Yii::$app->user->identity;
        $comments = $model->comments;
        return $comments;
    }

    public function actionFindByName($keyword)
    {
        return User::findByName($keyword);
    }

    public function actionRegister()
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
        $user->status = Yii::$app->params['api']['v1']['user_default_status'];
        $user->password = Yii::$app->security->generatePasswordHash($user->password);

        if (!$user->validate()) {
            return new ServerResponse(5, "Datos de registro incorrectos. Revisa la información e intentalo de nuevo más tarde", $user->errors);
        }
        $user->save(false);
        return ['auth_key' => $user->auth_key];
    }

    public function actionUpdateModel()
    {
        $user = Yii::$app->user->identity;
        $user->scenario = $user::SCENARIO_UPDATE;

        $params = Yii::$app->request->bodyParams; // Get data from xxx-form-url-encoded
        $user->attributes = $params;

        // If user wants to change password
        if (isset($params['password']) || isset($params['repeat_password'])) {
            if (!$user->validateModifiedPassword()) { // If passwords are not valid
                return new ServerResponse(5, "", $user->errors);
            }

            $user->password = Yii::$app->security->generatePasswordHash($user->password);
        }

        $user->updated_at = date("Y-m-d H-i-s");
        if (!$user->validate()) {
            return new ServerResponse(5, "", $user->errors);
        }

        $user->save(false);
        return $user;
    }

    public function actionUploadImage()
    {
        $user = Yii::$app->user->identity;
        $user->scenario = $user::SCENARIO_UPDATE;

        $old_profile_img = $user->profile_img;
        $old_background_img = $user->background_img;

        if ($background_img = UploadedFile::getInstanceByName('background_img')) {
            $user->setBackgroundImage($background_img);
            $user->save() && $this->removeImage($old_background_img);
        }

        if ($profile_img = UploadedFile::getInstanceByName('profile_img')) {
            $user->setProfileImage($profile_img);
            $user->save() && $this->removeImage($old_profile_img);
        }

        if (!$profile_img && !$background_img) {
            return new ServerResponse(5, "You must upload profile_img or background_img");
        }

        return new ServerResponse(1);
    }

    public function removeImage($file)
    {
        if ($file && $file != User::DEFAULT_PROFILE_IMG) {
            $src = User::IMG_DIR . $file;
            if (file_exists($src)) {
                unlink($src);
            }
        }
    }

    public function actionProfile()
    {
        return Yii::$app->user->identity;
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
            return new ServerResponse(34);
        }
        return new ServerResponse(1);
    }

    public function actionUnfollowUser($id)
    {
        $user = Yii::$app->user->identity;
        if ($model = FollowUsr::findOne(['follower' => $user->id, 'followed' => $id])) {
            if (!$model->delete()) {
                return new ServerResponse(10);
            }
        }

        return new ServerResponse(1);
    }

    public function actionFolloweds()
    {
        $user = Yii::$app->user->identity;
        return $user->followedUsers;
        /* $following = [];
    foreach ($user->followUsrs as $key => $value) {
    if ($value['accepted']) {
    array_push($following, $this->actionViewModel($value['followed']));
    }
    };
    return $following;
     */
    }

    public function actionFollowedsActivity()
    {
        $friends = Yii::$app->user->identity->followedUsers;
        $activity = [];
        foreach ($friends as $key => $friend) {
            $friend->lastComment ? array_push($activity[$friend->lastComment['date']], $friend->lastComment) : null;
            $friend->lastFollowedTvshow ? array_push($activity[$friend->lastFollowedTvshow['date']], $friend->lastComment) : null;
        }
        return $activity;
    }

}
