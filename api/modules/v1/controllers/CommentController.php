<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Comment;
use api\modules\v1\models\ServerResponse;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;

class CommentController extends ActiveController
{
    public $enableCsrfValidation = false;
    public $modelClass = 'api\modules\v1\models\Comment';

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
                'comment' => ['POST', 'OPTIONS'],
                'delete' => ['POST', 'OPTIONS'],
                'answer' => ['POST', 'OPTIONS'],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        //Eliminamos acciones de crear y eliminar apuntes. Eliminamos update para personalizarla
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['view']);
        return $actions;
    }

    public function actionComment($title, $season_num = 0, $episode_num = 0)
    {
        $fields = [];
        if (!isset($_POST['content'])) {
            return new ServerResponse(5);
        }

        $comment = new Comment();
        $fields['content'] = $_POST['content'];

        if (!$season_num && !$episode_num) {
            $fields['title'] = $title;
        } else {
            $fields['tvshow'] = $title;
            $fields['season_num'] = $season_num;
            $fields['episode_num'] = $episode_num;
        }

        $fields['author'] = Yii::$app->user->identity->id;
        $fields['date'] = date("Y-m-d H-i-s");
        $fields['visible'] = Yii::$app->params['api']['v1']['comment_default_visibility'];

        $comment->attributes = $fields;

        if ($comment->validate()) {
            $comment->save(false);
            return new ServerResponse(1);
        } else {
            return new ServerResponse(10, $comment->errors);
        }
    }

    public function actionDelete($id)
    {
        $response = [];

        if ($comment = Comment::findOne(['id' => $id])) {
            if ($comment->author = Yii::$app->user->identity->id) {
                $comment->content = "";
                $comment->save();
                return new ServerResponse(1);
            } else {
                return new ServerResponse(3);
            }
        } else {
            return new ServerResponse(34);
        }

        return $response;
    }

    public function actionAnswer($id)
    {

        if (!$answered = Comment::findOne(['id' => $id])) {
            return new ServerResponse(34);
        }

        $fields = $answered->attributes;
        $answer = new Comment;

        if (!isset($_POST['content'])) {
            $answer->addError('content', 'Debes especificar el contenido del comentario');
            return new ServerResponse(5, $answer->errors);
        }

        $answer->attributes = $fields;

        $answer->content = $_POST['content'];
        $answer->answer_to = $id;
        $answer->author = Yii::$app->user->identity->id;
        $answer->date = date("Y-m-d H-i-s");

        $answer->visible = Yii::$app->params['api']['v1']['comment_default_visibility'];

        if ($answer->validate()) {
            $answer->save(false);
            return new ServerResponse(1);
        } else {
            return new ServerResponse(10, $comment->errors);
        }
    }

}
