<?php

namespace backend\models;

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
            [['cache'], 'string', 'max' => 15000],
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['title' => 'id']);
    }

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
    public function getStoresTitles()
    {
        return $this->hasMany(StoresTitle::className(), ['title' => 'id']);
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
}
