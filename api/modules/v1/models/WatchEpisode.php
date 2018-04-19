<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "watch_episode".
 *
 * @property int $user
 * @property int $tvshow
 * @property int $season_num
 * @property int $episode_num
 * @property string $date
 * @property string $score
 *
 * @property Episode $tvshow0
 * @property User $user0
 */
class WatchEpisode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'watch_episode';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user', 'tvshow', 'season_num', 'episode_num'], 'required'],
            [['user', 'tvshow', 'season_num', 'episode_num'], 'integer'],
            [['date'], 'safe'],
            [['score'], 'number'],
            [['user', 'tvshow', 'season_num', 'episode_num'], 'unique', 'targetAttribute' => ['user', 'tvshow', 'season_num', 'episode_num']],
            [['tvshow', 'season_num', 'episode_num'], 'exist', 'skipOnError' => true, 'targetClass' => Episode::className(), 'targetAttribute' => ['tvshow' => 'tvshow', 'season_num' => 'season_num', 'episode_num' => 'episode_num']],
            [['user'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user' => Yii::t('app', 'User'),
            'tvshow' => Yii::t('app', 'Tvshow'),
            'season_num' => Yii::t('app', 'Season Num'),
            'episode_num' => Yii::t('app', 'Episode Num'),
            'date' => Yii::t('app', 'Date'),
            'score' => Yii::t('app', 'Score'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEpisodeModel()
    {
        return $this->hasOne(Episode::className(), ['tvshow' => 'tvshow', 'season_num' => 'season_num', 'episode_num' => 'episode_num']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserModel()
    {
        return $this->hasOne(User::className(), ['id' => 'user']);
    }
}
