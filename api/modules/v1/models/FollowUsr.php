<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "follow_usr".
 *
 * @property int $follower
 * @property int $followed
 * @property int $accepted
 *
 * @property User $followed0
 * @property User $follower0
 */
class FollowUsr extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'follow_usr';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['follower', 'followed'], 'required'],
            [['follower', 'followed'], 'integer'],
            [['accepted'], 'string', 'max' => 1],
            [['follower', 'followed'], 'unique', 'targetAttribute' => ['follower', 'followed']],
            [['followed'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['followed' => 'id']],
            [['follower'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['follower' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'follower' => Yii::t('app', 'Follower'),
            'followed' => Yii::t('app', 'Followed'),
            'accepted' => Yii::t('app', 'Accepted'),
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    /* public function getFollowed0()
    {
        return $this->hasOne(User::className(), ['id' => 'followed']);
    } */

    /**
     * @return \yii\db\ActiveQuery
     */
    /* public function getFollower0()
    {
        return $this->hasOne(User::className(), ['id' => 'follower']);
    } */
}
