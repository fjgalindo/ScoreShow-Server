<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "platform".
 *
 * @property int $id
 * @property string $name
 * @property string $logo
 * @property string $website
 *
 * @property StoresEpisode[] $storesEpisodes
 * @property Episode[] $tvshows
 * @property StoresTitle[] $storesTitles
 * @property Title[] $titles
 */
class Platform extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'platform';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 30],
            [['logo', 'website'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'logo' => Yii::t('app', 'Logo'),
            'website' => Yii::t('app', 'Website'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStoresEpisodes()
    {
        return $this->hasMany(StoresEpisode::className(), ['platform' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTvshows()
    {
        return $this->hasMany(Episode::className(), ['tvshow' => 'tvshow', 'season_num' => 'season_num', 'episode_num' => 'episode_num'])->viaTable('stores_episode', ['platform' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStoresTitles()
    {
        return $this->hasMany(StoresTitle::className(), ['platform' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTitles()
    {
        return $this->hasMany(Title::className(), ['id' => 'title'])->viaTable('stores_title', ['platform' => 'id']);
    }
}
