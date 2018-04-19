<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "follow_title".
 *
 * @property int $title
 * @property int $user
 * @property string $date
 *
 * @property Title $title0
 * @property User $user0
 */
class FollowTitle extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'follow_title';
    }

    public static function primaryKey()
    {
         return array('title', 'user');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'user'], 'required'],
            [['title', 'user'], 'integer'],
            [['date'], 'safe'],
            [['title', 'user'], 'unique', 'targetAttribute' => ['title', 'user']],
            [['title'], 'exist', 'skipOnError' => true, 'targetClass' => Title::className(), 'targetAttribute' => ['title' => 'id']],
            [['user'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'title' => Yii::t('app', 'Title'),
            'user' => Yii::t('app', 'User'),
            'date' => Yii::t('app', 'Date'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTitle0()
    {
        return $this->hasOne(Title::className(), ['id' => 'title']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser0()
    {
        return $this->hasOne(User::className(), ['id' => 'user']);
    }
}
