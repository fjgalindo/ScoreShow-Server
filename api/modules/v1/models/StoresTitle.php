<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "stores_title".
 *
 * @property int $platform
 * @property int $title
 * @property string $link
 *
 * @property Platform $platform0
 * @property Title $title0
 */
class StoresTitle extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stores_title';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['platform', 'title'], 'required'],
            [['platform', 'title'], 'integer'],
            [['link'], 'string', 'max' => 150],
            [['platform', 'title'], 'unique', 'targetAttribute' => ['platform', 'title']],
            [['platform'], 'exist', 'skipOnError' => true, 'targetClass' => Platform::className(), 'targetAttribute' => ['platform' => 'id']],
            [['title'], 'exist', 'skipOnError' => true, 'targetClass' => Title::className(), 'targetAttribute' => ['title' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'platform' => Yii::t('app', 'Platform'),
            'title' => Yii::t('app', 'Title'),
            'link' => Yii::t('app', 'Link'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlatform0()
    {
        return $this->hasOne(Platform::className(), ['id' => 'platform']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTitle0()
    {
        return $this->hasOne(Title::className(), ['id' => 'title']);
    }
}
