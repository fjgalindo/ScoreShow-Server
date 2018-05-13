<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "movie".
 *
 * @property int $id
 *
 * @property Title $id0
 * @property WatchMovie[] $watchMovies
 * @property User[] $users
 */
class Movie extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'movie';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
            [['id'], 'unique'],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => Title::className(), 'targetAttribute' => ['id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTitle()
    {
        return $this->hasOne(Title::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWatchMovies()
    {
        return $this->hasMany(WatchMovie::className(), ['movie' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user'])->viaTable('watch_movie', ['movie' => 'id']);
    }

    public function isWatched()
    {
        if (WatchMovie::findOne(
            [
                'movie' => $this->id,
                'user' => Yii::$app->user->identity->id,
            ]
        )) {
            return true;
        }
        return false;
    }

    public function getWatchMovie()
    {
        return WatchMovie::findOne(
            [
                'movie' => $this->id,
                'user' => Yii::$app->user->identity->id,
            ]
        );
    }

    public static function existsOnTmdb($id_tmdb)
    {
        if (Yii::$app->TMDb->getTitleData($id, 'movie', ['credits', 'images'])) {
            return true;
        }
        return false;
    }

    public function getTmdbData()
    {
        return Yii::$app->TMDb->getTitleData($this->title->id_tmdb, 'movie', ['credits', 'images'], false);
    }

    public function isReleased()
    {
        $cache = $this->title->cache ?
        $this->title->cache
        : json_decode($this->getTmdbData(), true);

        $release = strtotime($cache['release_date']);
        $today = strtotime(date("Y-m-d"));
        if ($release > $today) {
            return false;
        }
        return true;
    }

    public function updateCache()
    {
        Yii::$app->controller->module->runAction(
            'title/update-cache', ['title_id' => $this->id]);
        return Movie::findOne($this->id);
    }

    public function getMyScore()
    {
        if ($model = WatchMovie::findOne(
            [
                'movie' => $this->id,
                'user' => Yii::$app->user->identity->id,
            ]
        )) {
            return floatval($model->score);
        } else {
            return 0;
        }
    }

    public function getByTMDbId($id_tmdb)
    {
        return Title::find()
            ->where('`title`.`id_tmdb` = :id_tmdb AND `title`.`id` IN (select `id` from `movie`)')
            ->addParams([':id_tmdb' => $id_tmdb]) // Avoid SQL Injections
            ->one();
    }
}
