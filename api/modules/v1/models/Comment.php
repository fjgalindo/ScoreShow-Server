<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "comment".
 *
 * @property int $id
 * @property int $author
 * @property int $title
 * @property int $tvshow
 * @property int $season_num
 * @property int $episode_num
 * @property string $date
 * @property int $answer_to
 * @property string $content
 * @property int $visible
 *
 * @property Comment $answerTo
 * @property Comment[] $comments
 * @property User $author0
 * @property Episode $tvshow0
 * @property Title $title0
 * @property Report[] $reports
 */
class Comment extends \yii\db\ActiveRecord
{

    public $answers; // Answers variable used when needed

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['author', 'title', 'tvshow', 'season_num', 'episode_num', 'answer_to'], 'integer'],
            [['date', 'content', 'visible'], 'required'],
            [['date'], 'safe'],
            [['content'], 'string', 'max' => 300],
            [['visible'], 'boolean'],
            [['answer_to'], 'exist', 'skipOnError' => true, 'targetClass' => Comment::className(), 'targetAttribute' => ['answer_to' => 'id']],
            [['author'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author' => 'id']],
            [['tvshow', 'season_num', 'episode_num'], 'exist', 'skipOnError' => true, 'targetClass' => Episode::className(), 'targetAttribute' => ['tvshow' => 'tvshow', 'season_num' => 'season_num', 'episode_num' => 'episode_num']],
            [['title'], 'exist', 'skipOnError' => true, 'targetClass' => Title::className(), 'targetAttribute' => ['title' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'author' => Yii::t('app', 'Author'),
            'title' => Yii::t('app', 'Title'),
            'tvshow' => Yii::t('app', 'Tvshow'),
            'season_num' => Yii::t('app', 'Season Num'),
            'episode_num' => Yii::t('app', 'Episode Num'),
            'date' => Yii::t('app', 'Date'),
            'answer_to' => Yii::t('app', 'Answer To'),
            'content' => Yii::t('app', 'Content'),
            'visible' => Yii::t('app', 'Visible'),
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAnswerTo()
    {
        return $this->hasOne(Comment::className(), ['id' => 'answer_to']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['answer_to' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor0()
    {
        return $this->hasOne(User::className(), ['id' => 'author']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTvshow0()
    {
        return $this->hasOne(Episode::className(), ['tvshow' => 'tvshow', 'season_num' => 'season_num', 'episode_num' => 'episode_num']);
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
    public function getReports()
    {
        return $this->hasMany(Report::className(), ['comment' => 'id']);
    }

    public function getAnswers()
    {
        //if ($this->tvshow && $this->season_num && $this->episode_num && !$this->title) {
        if ($this->title) {
            return Comment::find()->where(['title' => $this->title, 'visible' => 1, 'answer_to' => $this->id])->orderBy(['date' => SORT_DESC])->all();
        } else {
            return Comment::find()->where(['tvshow' => $this->tvshow, 'season_num' => $this->season_num, 'episode_num' => $this->episode_num, 'visible' => 1, 'answer_to' => $this->id])->orderBy(['date' => SORT_DESC])->all();
        }
    }
}
