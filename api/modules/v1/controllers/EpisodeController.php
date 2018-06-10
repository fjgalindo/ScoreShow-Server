<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Episode;
use api\modules\v1\models\FollowTitle;
use api\modules\v1\models\ServerResponse;
use api\modules\v1\models\Title;
use api\modules\v1\models\Tvshow;
use api\modules\v1\models\User;
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
                'watch-season' => ['POST', 'OPTIONS'],
                'unwatch' => ['POST', 'OPTIONS'],
                'unwatch-season' => ['POST', 'OPTIONS'],
                'view-comments' => ['GET', 'OPTIONS'],
                'comment' => ['POST', 'OPTIONS'],
                'last-comments' => ['GET', 'OPTIONS'],
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

        // Si el episodio no existe en la base de datos, el servidor devuelve un error específico.
        if (!$model = Episode::findOne(['tvshow' => $id, 'season_num' => $season, 'episode_num' => $ep])) {
            return new ServerResponse(34);
        }

        if ($model->needsUpdate()) { // Si el episodio necesita actualizarse, actualiza el campo cache
            if (!$model = $this->updateCache($model->tvshow, $model->season_num, $model->episode_num)) {
                return new ServerResponse(10);
            }
        }

        $response = $model->cache;
        $response['watched'] = $model->watched;
        $response['tvshow_id'] = $id;
        $response['myscore'] = $model->myScore;

        return $response;
    }

    public function actionLastComments($id, $season, $episode)
    {
        if (!$episode = Episode::findOne(['tvshow' => $id, 'season_num' => $season, 'episode_num' => $episode])) {
            return new ServerResponse(34);
        }

        $response = [];
        foreach ($episode->lastComments as $key => $comment) {
            $response[$key] = $comment;
            $response[$key]['author'] = User::findOne($comment['author']);
        }

        return $response;
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
        if (!$episode = Episode::findOne(['tvshow' => $id, 'season_num' => $season, 'episode_num' => $ep])) {
            return new ServerResponse(34);
        }

        if (!$episode->isReleased()) {
            return new ServerResponse(14);
        }

        return Yii::$app->controller->module->runAction(
            'watch-episode/watch', ['id' => $episode->tvshow, 'season' => $season, 'ep' => $ep]
        );
    }

    public function actionUnwatch($id, $season, $ep)
    {
        if (!$episode = Episode::findOne(['tvshow' => $id, 'season_num' => $season, 'episode_num' => $ep])) {
            return new ServerResponse(34);
        }

        return Yii::$app->controller->module->runAction(
            'watch-episode/unwatch', ['id' => $episode->tvshow, 'season' => $season, 'ep' => $ep]
        );

    }

    public function actionWatchSeason($id, $season)
    {
        $uid = Yii::$app->user->identity->id;
        if (!$episodes = Episode::find()->where(['tvshow' => $id, 'season_num' => $season])->all()) {
            if (!$this->updateSeason($id, $season)) {
                return new ServerResponse(34);
            } else {
                $episodes = Episode::find()->where(['tvshow' => $id, 'season_num' => $season])->all();
            }
        }

        if (!$model = FollowTitle::findOne(
            [
                'title' => $id, 'user' => $uid,
            ])
        ) {
            Yii::$app->controller->module->runAction(
                'follow-title/follow', ['title_id' => $id]
            );
        }

        $bulkdata = [];
        $released = true;
        for ($i = 0; $i < count($episodes) && $released; $i++) {
            $episode = $episodes[$i];
            if ($episode->isReleased()) {
                if (!$episode->watched) {
                    array_push($bulkdata,
                        [$uid, $episode->tvshow, $episode->season_num, $episode->episode_num, date("Y-m-d H-i-s")]
                    );
                }
            } else {
                $released = false;
            }
        }
        $num = null;
        if (count($bulkdata)) {
            $num = Yii::$app->db
                ->createCommand()
                ->batchInsert(WatchEpisode::tableName(), ['user', 'tvshow', 'season_num', 'episode_num', 'date'], $bulkdata)
                ->execute();
        }
        return new ServerResponse(1);
    }

    public function actionUnwatchSeason($id, $season)
    {
        $uid = Yii::$app->user->identity->id;
        if ($episodes_watched = WatchEpisode::find()->where(['user' => $uid, 'tvshow' => $id, 'season_num' => $season])->all()) {
            WatchEpisode::deleteAll(['user' => $uid, 'tvshow' => $id, 'season_num' => $season]);
        }

        return new ServerResponse(1);
    }

    public function actionListSeason($id, $season)
    {
        if (!$tvshow = Tvshow::findOne($id)) {
            return new ServerResponse(34);
        }

        $id_tmdb = $tvshow->title->id_tmdb;

        $response = Yii::$app->TMDb->getSeasonData($id_tmdb, $season);
        $response['tvshow_id'] = $tvshow->id;
        $response['completed'] = false;

        $watchedN = 0;
        foreach ($response['episodes'] as $key => $episode) {
            if (!Episode::findOne(['tvshow' => $id, 'season_num' => $season, 'episode_num' => $episode['episode_number']])) {
                $this->addEpisode($tvshow, $season, $episode['episode_number'], $episode);
            } else {
                if ($watch = WatchEpisode::findOne(
                    [
                        'tvshow' => $id,
                        'season_num' => $season,
                        'episode_num' => $episode['episode_number'],
                        'user' => Yii::$app->user->identity->id,
                    ])) {
                    $watchedN++;
                    $response['episodes'][$key]['watched'] = true;
                    $response['episodes'][$key]['myscore'] = $watch->score;
                } else {
                    $response['episodes'][$key]['watched'] = false;
                    $response['episodes'][$key]['myscore'] = false;

                }
            }
        }

        if ($watchedN === count($response['episodes'])) {
            $response['completed'] = true;
        }

        return $response;
    }

    public function actionViewComments($id, $season, $episode)
    {
        if (!$model = Episode::findOne(['tvshow' => $id, 'season_num' => $season, 'episode_num' => $episode])) {
            return new ServerResponse(34);
        }

        $response = [];
        foreach ($model->comments as $i => $comment) {
            $response[$i] = $comment;
            $response[$i]['author'] = User::findOne($comment['author']);
            foreach ($comment['answers'] as $j => $answer) {
                $response[$i]['answers'][$j]['author'] = User::findOne($answer['author']);
            }
        }

        return $response;

    }

    public function actionComment($id, $season, $episode)
    {
        $response = [];
        $comment = [];
        if (!$model = Episode::findOne(['tvshow' => $id, 'season_num' => $season, 'episode_num' => $episode])) {
            return new ServerResponse(34);
        } /*
        if (!isset($_POST['content'])) {
        return new ServerResponse(5, "content value invalid");
        }

        $content = $_POST['content']; */

        $comment['title'] = $id;
        $comment['season_num'] = $season;
        $comment['episode_num'] = $episode;
        // $comment['content'] = $content;

        $response = Yii::$app->controller->module->runAction(
            'comment/comment', $comment
        );

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
            if ($season['episodes']) {
                if (!count($season['episodes'])) {
                    $last_season = array_reverse($title['cache']['seasons'])[1];
                    $season = Yii::$app->TMDb->getSeasonData($title->id_tmdb, $last_season['season_number']);
                }
            }

            $released = true;
            for ($i = 0; $i < count($season['episodes']) && $released; $i++) {
                $episode = $season['episodes'][$i];
                $today = date("Y-m-d");
                $air_date = $episode['air_date'];
                if ($air_date >= $today) {
                    $released = false;
                    if (!isset($pending[$episode['air_date']])) {
                        $pending[$episode['air_date']] = [];
                    }
                    if (!$model = Episode::findOne(['tvshow' => $tvshow->id, 'season_num' => $episode['season_number'], 'episode_num' => $episode['episode_number']])) {
                        if (!$model = $this->addEpisode($tvshow, $episode['season_number'], $episode['episode_number'], $episode)) {
                            return new ServerResponse(10);
                        }
                    }
                    $item = [];
                    $item['tvshow'] = $tvshow->title->cache;
                    $item['tvshow']['_id'] = $tvshow->title->id;
                    $item['episode'] = $episode;
                    $item['episode']['tvshow_id'] = $tvshow->title->id;
                    array_push($pending[$episode['air_date']], $item);
                }
            }
        }
        ksort($pending);
        return $pending;
    }

    public function updateSeason($id, $season)
    {
        $tvshow = Tvshow::findOne(['id' => $id]);
        $season_data = Yii::$app->TMDb->getSeasonData($tvshow->title->id_tmdb, $season);
        foreach ($season_data['episodes'] as $episode) {
            if (!Episode::findOne(['tvshow' => $id, 'season_num' => $season, 'episode_num' => $episode['episode_number']])) {
                $res = $this->addEpisode($tvshow, $season, $episode['episode_number'], $episode);
            } else {
                $res = $this->updateCache($tvshow, $season, $episode['episode_number']);
            }

            if (!$res) {
                return new ServerResponse(10);
            }
        }
        return true;
    }

    public function updateCache($tvshow, $season, $ep)
    {
        $episode = Episode::findOne(['tvshow' => $tvshow, 'season_num' => $season, 'episode_num' => $ep]);

        $episode->cache = json_encode($episode->getTMDbData());

        $episode->last_update = date("Y-m-d H-i-s");

        if ($episode->save()) {
            return $episode;
        }

        return false;
    }

    public function addEpisode($tvshow, $season_num, $episode_num, $cache = null)
    {
        $episode = new Episode();
        $episode->tvshow = $tvshow->id;
        $episode->season_num = $season_num;
        $episode->episode_num = $episode_num;

        $cache
        ? $episode->cache = json_encode($cache)
        : $episode->cache = Yii::$app->TMDb->getEpisodeData($tvshow->title->id_tmdb, $season_num, $episode_num, false);

        //$episode->last_update = date("Y-m-d H-i-s");

        if (!$episode->validate()) {
            return false;
        }
        $episode->save(false);
        return $episode;
    }

}
