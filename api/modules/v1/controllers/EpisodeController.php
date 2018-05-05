<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Episode;
use api\modules\v1\models\ServerResponse;
use api\modules\v1\models\Tvshow;
use api\modules\v1\models\WatchEpisode;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;

class EpisodeController extends ActiveController
{
    public $enableCsrfValidation = false;
    public $modelClass = 'api\modules\v1\models\Episode';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['http://localhost:8100', '*'],
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
                'add' => ['POST', 'OPTIONS'],
                'view-model' => ['GET', 'OPTIONS'],
                'score' => ['POST', 'OPTIONS'],
                'watch' => ['POST', 'OPTIONS'],
                'unwatch' => ['POST', 'OPTIONS'],
                'view-comments' => ['GET', 'OPTIONS'],
                'comment' => ['POST', 'OPTIONS'],
                'list-season' => ['GET', 'OPTIONS'],
                'premieres' => ['GET', 'OPTIONS'],
                '*' => ['OPTIONS'],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        //Eliminamos acciones de crear y eliminar apuntes. Eliminamos update para personalizarla.
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['view'], $actions['index']);

        return $actions;
    }

    public function actionViewModel($id, $season, $ep)
    {
        $response = [];

        if (!$model = Episode::findOne(['tvshow' => $id, 'season_num' => $season, 'episode_num' => $ep])) {
            return new ServerResponse(34);
        }

        if ($model->needsUpdate()) {
            if (!$model = $this->updateCache($model->tvshow, $model->season_num, $model->episode_num)) {
                return new ServerResponse(10);
            }
        }

        $response = json_decode($model->cache, true);

        $response['watched'] = $model->isWatched();
        $response['tvshow_id'] = $id;
        /*
        $response['last_comments'] = $model->getLastComments();
        $response['platforms'] = $model->platformLinks;
         */
        $response['myscore'] = $model->myScore;

        return $response;
    }

    public function actionGetLastcomments($id, $season_num, $episode_num)
    {
        if (!$episode = Episode::findOne(['tvshow' => $id, 'season_num' => $season_num, 'episode_num' => $episode_num])) {
            return new ServerResponse(34);
        }

        return $episode->lastComments;
    }

    public function actionPlatforms($id)
    {
        if (!$episode = Episode::findOne(['tvshow' => $id, 'season_num' => $season_num, 'episode_num' => $episode_num])) {
            return new ServerResponse(34);
        }

        return $episode->platformLinks;

    }

    public function actionScore($id, $season, $ep)
    {
        $score = isset($_POST['score']) ? $_POST['score'] : null;

        if ($score === null) {
            return new ServerResponse(5, ['score' => 'Field score is required']);
        } else if (!is_numeric($score)) {
            return new ServerResponse(5, ['score' => 'Field score should be a number']);
        } else if ($score < 0.5 || $score > 10) {
            return new ServerResponse(18);
        }

        if (!$episode = Episode::findOne(
            ['tvshow' => $id, 'season_num' => $season, 'episode_num' => $ep]
        )) {
            return new ServerResponse(34);
        }

        if (!$episode->isReleased()) {
            return new ServerResponse(14);
        }

        return Yii::$app->controller->module->runAction(
            'watch-episode/score',
            [
                'id' => $episode->tvshow,
                'season' => $episode->season_num,
                'ep' => $episode->episode_num,
                'score' => $score,
            ]
        );

    }

    public function actionWatch($id, $season, $ep)
    {
        if (!$tv_episode = Episode::findOne(['tvshow' => $id, 'season_num' => $season, 'episode_num' => $ep])) { // Check if movie exists
            return new ServerResponse(34);
        }

        if (!$tv_episode->isReleased()) {
            return new ServerResponse();
        }

        return Yii::$app->controller->module->runAction(
            'watch-episode/watch', ['id' => $tv_episode->tvshow, 'season' => $season, 'ep' => $ep]
        );
    }

    public function actionUnwatch($id, $season, $ep)
    {
        if (!$tv_episode = Episode::findOne(['tvshow' => $id, 'season_num' => $season, 'episode_num' => $ep])) {
            return new ServerResponse(34);
        }

        return Yii::$app->controller->module->runAction(
            'watch-episode/unwatch', ['id' => $tv_episode->tvshow, 'season' => $season, 'ep' => $ep]
        );

    }
/*
public function actionWatchSeason($id, $season)
{
$uid = Yii::$app->user->identity->id;
if (!$episodes = Episode::find()->where(['tvshow' => $id, 'season_num' => $season])->all()) {
return new ServerResponse(34);
}

foreach ($episodes as $key => $episode) {
// If is released and I don't check it as watched
if ($episode->isReleased() && !$model = WatchEpisode::findOne(
[
'user' => $uid,
'tvshow' => $id, 'season_num' => $season, 'episode_num' => $episode->episode_num,
])) {
$model = new WatchEpisode();
$model->user = $uid;
$model->tvshow = $episode->tvshow;
$model->season_num = $episode->season_num;
$model->episode_num = $episode->episode_num;
$model->date = date("Y-m-d H-i-s");

if (!$model->save()) {
return new ServerResponse(10);
}
}
}

return new ServerResponse(1);
}

public function actionUnwatchSeason($id, $season)
{
$uid = Yii::$app->user->identity->id;
if ($episodes_watched = WatchEpisode::find()->where(['user' => $uid, 'tvshow' => $id, 'season_num' => $season])->all()) {
foreach ($episodes_watched as $key => $episode) {
$episode->delete();
}
}

return new ServerResponse(1);
}
 */
    public function actionListSeason($id, $season)
    {
        if (!$tvshow = Tvshow::findOne($id)) {
            return new ServerResponse(34);
        }

        $id_tmdb = $tvshow->title->id_tmdb;
        $response = Yii::$app->TMDb->getSeasonData($id_tmdb, $season);
        $response['tvshow_id'] = $tvshow->id;
        foreach ($response['episodes'] as $key => $episode) {
            if (!Episode::findOne(['tvshow' => $id, 'season_num' => $season, 'episode_num' => $episode['episode_number']])) {
                $this->addEpisode($tvshow, $season, $episode['episode_number']);
            } else {
                if ($watch = WatchEpisode::findOne(
                    [
                        'tvshow' => $id,
                        'season_num' => $season,
                        'episode_num' => $episode['episode_number'],
                        'user' => Yii::$app->user->identity->id,
                    ])) {
                    $response['episodes'][$key]['watched'] = $watch->date;
                    $response['episodes'][$key]['myscore'] = $watch->score;
                } else {
                    $response['episodes'][$key]['watched'] = false;
                    $response['episodes'][$key]['myscore'] = false;
                }
            }
        }

        return $response;
    }

    public function actionViewComments($id, $season, $episode)
    {
        if (!$model = Episode::findOne(['tvshow' => $id, 'season_num' => $season, 'episode_num' => $episode])) {
            return new ServerResponse(34);
        }
        return $model->comments;
    }

    public function actionComment($id, $season, $episode)
    {
        $response = [];
        $comment = [];
        if ($model = Episode::findOne(['tvshow' => $id, 'season_num' => $season, 'episode_num' => $episode])) {
            if (isset($_POST['content'])) {

                $content = $_POST['content']; // PROCESS THE TEXT CONTAINED HERE

                $comment['title'] = $id;
                $comment['season_num'] = $season;
                $comment['episode_num'] = $episode;
                $comment['content'] = $content;
                /*
                if (isset($_POST['answer_to'])) {
                $comment['answer_to'] = $_POST['answer_to'];
                }*/

                $response = Yii::$app->controller->module->runAction(
                    'comment/comment', $comment
                );

            } else {
                $response['message'] = "Falta el contenido del comentario";
                $response['error'] = "53";
            }
        } else {
            $response['message'] = "Error: La película con id $id no existe";
            $response['error'] = "32";
        }

        return $response;
    }

    public function actionPremieres()
    {
        $user = Yii::$app->user->identity;
        $tvshows = $user->tvshows;
        $pending = [];

        foreach ($tvshows as $key => $tvshow) {
            $title = $tvshow->title;
            $last_season = array_reverse($title['cache']['seasons'])[0];
            $season = Yii::$app->TMDb->getSeasonData($title->id_tmdb, $last_season['season_number']);

            $released = true;
            for ($i = 0; $i < count($season['episodes']) && $released; $i++) {
                $episode = $season['episodes'][$i];

                $today = strtotime(date("Y-m-d"));
                $air_date = strtotime($episode['air_date']);

                if ($air_date >= $today) {
                    $released = false;
                    //echo "Checking " . $title->cache['name'] . " ===> ";
                    $pending[$episode['air_date']] = [];
                    if (!$model = Episode::findOne(['tvshow' => $tvshow->id, 'season_num' => $episode['season_number'], 'episode_num' => $episode['episode_number']])) {
                        if (!$model = $this->addEpisode($tvshow, $episode['season_number'], $episode['episode_number'])) {
                            return new ServerResponse(10);
                        }
                    }
                    $item = [];
                    $item['tvshow'] = $tvshow->title->cache;
                    $item['tvshow']['tvshow_id'] = $tvshow->title->id;
                    $item['episode'] = $episode;
                    array_push($pending[$episode['air_date']], $item);
                }
            }
        }
        ksort($pending);

        return $pending;
    }

    public function updateCache($tvshow, $season, $ep)
    {
        $episode = Episode::findOne(['tvshow' => $tvshow, 'season_num' => $season, 'episode_num' => $ep]);

        $episode->cache = json_encode($episode->getTMDbData(), true);

        $episode->last_update = date("Y-m-d H-i-s");

        if ($episode->save()) {
            return $episode;
        }

        return false;
    }

    public function updateSeason($id, $season)
    {
        $episode = Episode::findOne(['tvshow' => $tvshow, 'season_num' => $season, 'episode_num' => $ep]);

        $episode->cache = json_encode($episode->getTMDbData());

        $episode->last_update = date("Y-m-d H-i-s");

        if ($episode->save()) {
            return $episode;
        }

        return false;
    }

    public function addEpisode($tvshow, $season_num, $episode_num)
    {
        $episode = new Episode();
        $episode->tvshow = $tvshow->id;
        $episode->season_num = $season_num;
        $episode->episode_num = $episode_num;
        $episode->cache = Yii::$app->TMDb->getEpisodeData($tvshow->title->id_tmdb, $season_num, $episode_num);
        //$new_episode->cache = json_encode($response['episodes'][$key]);

        $episode->last_update = date("Y-m-d H-i-s");

        // Don't set last_update field, so on action view it will update cache adding some extras
        if (!$episode->validate()) {
            return false;
        }
        //$episode->save(false);
        return $episode;
    }

}
