<?php

namespace api\modules\v1\models;

use Yii;
use yii\filters\auth\HttpBearerAuth;

/**
 * This is the model class for table "watch_movie".
 *
 * @property int $user
 * @property int $movie
 * @property string $date
 * @property string $score
 *
 * @property Movie $movie0
 * @property User $user0
 */
class WatchMovie extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'watch_movie';
    }

    public function beforeSave($insert)
    {
        // If user is not following movie...
        if(!$model = FollowTitle::findOne(['user' => Yii::$app->user->identity->id, 'title' => $this->movie])){
            Yii::$app->controller->module->runAction(
                'follow-title/follow', ['title_id' => $this->movie]
            );
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user', 'movie'], 'required'],
            [['user', 'movie'], 'integer'],
            [['date'], 'safe'],
            [['score'], 'number'],
            [['user', 'movie'], 'unique', 'targetAttribute' => ['user', 'movie']],
            [['movie'], 'exist', 'skipOnError' => true, 'targetClass' => Movie::className(), 'targetAttribute' => ['movie' => 'id']],
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
            'movie' => Yii::t('app', 'Movie'),
            'date' => Yii::t('app', 'Date'),
            'score' => Yii::t('app', 'Score'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMovieModel()
    {
        return $this->hasOne(Movie::className(), ['id' => 'movie']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserModel()
    {
        return $this->hasOne(User::className(), ['id' => 'user']);
    }
}
