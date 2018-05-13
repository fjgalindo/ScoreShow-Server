<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "title".
 *
 * @property int $id
 * @property int $id_tmdb
 * @property string $cache
 * @property string $last_update
 *
 * @property Comment[] $comments
 * @property FollowTitle[] $followTitles
 * @property User[] $users
 * @property Movie $movie
 * @property StoresTitle[] $storesTitles
 * @property Platform[] $platforms
 * @property Tvshow $tvshow
 */
class Title extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'title';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_tmdb'], 'required'],
            [['id_tmdb'], 'integer'],
            [['last_update'], 'safe'],
            [['cache'], 'string', 'max' => 120000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'id_tmdb' => Yii::t('app', 'Id Tmdb'),
            'cache' => Yii::t('app', 'Cache'),
            'last_update' => Yii::t('app', 'Last Update'),
        ];
    }

    public function afterFind()
    {
        $this->cache = json_decode($this->cache, true); // Formats JSON string into JSON object
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->cache = json_decode($this->cache, true); // Formats JSON string into JSON object
        return $insert;
    }

    /**
     * @return \yii\db\ActiveQuery
     *//*
    public function getComments()
    {
    return $this->hasMany(Comment::className(), ['title' => 'id']);
    }*/

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollowTitles()
    {
        return $this->hasMany(FollowTitle::className(), ['title' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user'])->viaTable('follow_title', ['title' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMovie()
    {
        return $this->hasOne(Movie::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlatformLinks()
    {
        return (new \yii\db\Query())
            ->select(['`platform`.`name`', '`stores_title`.`link`'])
            ->from('`stores_title`, `platform`')
            ->where('`stores_title`.`title` = :id AND `platform`.`id` = `stores_title`.`platform`')
            ->addParams([':id' => $this->id]) // Avoid SQL Injections
            ->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlatforms()
    {
        return $this->hasMany(Platform::className(), ['id' => 'platform'])->viaTable('stores_title', ['title' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTvshow()
    {
        return $this->hasOne(Tvshow::className(), ['id' => 'id']);
    }

    public function getLastComments()
    {
        return Comment::find()
            ->where([
                'title' => $this->id,
                'visible' => 1,
                'answer_to' => null,
            ])->limit(3)->orderBy(['date' => SORT_DESC])->all();
    }

    public function getComments()
    {
        /* METHOD 1 */
        $response = [];
        $comments = Comment::find()->where(['title' => $this->id, 'visible' => 1, 'answer_to' => null])->orderBy(['date' => SORT_DESC])->all();

        foreach ($comments as $key => $value) {
            $comment = $value->toArray();
            $comment['answers'] = $value->getAnswers();
            array_push($response, $comment);
        }

        return $response;
    }

    public function isFollowedByUser()
    {
        if ($result = (new \yii\db\Query())
            ->select(['`follow_title`.*'])
            ->from('`follow_title`')
            ->where('`follow_title`.`title` = :title AND `follow_title`.`user` = :user')
            ->addParams([':title' => $this->id, ':user' => Yii::$app->user->identity->id]) // Avoid SQL Injections
            ->one()) {
            return true;
        }

        return false;
    }

    public function needsUpdate()
    {
        $last_update = strtotime("$this->last_update " . Yii::$app->params['api']['v1']['timeToRefeshCache']);
        $today = strtotime(date("Y-m-d"));

        if (!$this->last_update || !$this->cache || $last_update <= $today) {
            return true;
        }
        return false;
    }

}
