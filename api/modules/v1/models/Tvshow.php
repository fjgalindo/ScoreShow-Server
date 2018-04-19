<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tvshow".
 *
 * @property int $id
 *
 * @property Episode[] $episodes
 * @property Title $id0
 */
class Tvshow extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tvshow';
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
    public function getEpisodes()
    {
        return $this->hasMany(Episode::className(), ['tvshow' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTitle()
    {
        return $this->hasOne(Title::className(), ['id' => 'id']);
    }

    public function getTvShowByTMDbId($id_tmdb){
        return Title::find()
                ->where('`title`.`id_tmdb` = :id_tmdb AND `title`.`id` IN (select `id` from `tvshow`)')
                ->addParams([':id_tmdb' => $id_tmdb]) // Avoid SQL Injections
                ->one();
    }

    public function getTmdbData(){
        return Yii::$app->TMDb->getTitleData($this->title->id_tmdb, 'tv', ['credits', 'images'], false);
    }
}
