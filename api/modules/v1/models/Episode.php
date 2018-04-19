<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "episode".
 *
 * @property int $tvshow
 * @property int $season_num
 * @property int $episode_num
 *
 * @property Comment[] $comments
 * @property Tvshow $tvshow0
 * @property StoresEpisode[] $storesEpisodes
 * @property Platform[] $platforms
 * @property WatchEpisode[] $watchEpisodes
 * @property User[] $users
 */
class Episode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'episode';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tvshow', 'season_num', 'episode_num'], 'required'],
            [['tvshow', 'season_num', 'episode_num'], 'integer'],
            [['tvshow', 'season_num', 'episode_num'], 'unique', 'targetAttribute' => ['tvshow', 'season_num', 'episode_num']],
            [['tvshow'], 'exist', 'skipOnError' => true, 'targetClass' => Tvshow::className(), 'targetAttribute' => ['tvshow' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tvshow' => Yii::t('app', 'Tvshow'),
            'season_num' => Yii::t('app', 'Season Num'),
            'episode_num' => Yii::t('app', 'Episode Num'),
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     *//*
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['tvshow' => 'tvshow', 'season_num' => 'season_num', 'episode_num' => 'episode_num']);
    }*/

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTvshowModel()
    {
        return $this->hasOne(Tvshow::className(), ['id' => 'tvshow']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStoresEpisodes()
    {
        return $this->hasMany(StoresEpisode::className(), ['tvshow' => 'tvshow', 'season_num' => 'season_num', 'episode_num' => 'episode_num']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlatforms()
    {
        return $this->hasMany(Platform::className(), ['id' => 'platform'])->viaTable('stores_episode', ['tvshow' => 'tvshow', 'season_num' => 'season_num', 'episode_num' => 'episode_num']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWatchEpisodes()
    {
        return $this->hasMany(WatchEpisode::className(), ['tvshow' => 'tvshow', 'season_num' => 'season_num', 'episode_num' => 'episode_num']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user'])->viaTable('watch_episode', ['tvshow' => 'tvshow', 'season_num' => 'season_num', 'episode_num' => 'episode_num']);
    }


    public function getMyScore()
    {
        if($model = WatchEpisode::findOne(
        [
                'tvshow'=>$this->tvshow,
                'season_num' => $this->season_num,
                'episode_num' => $this->episode_num,
                'user' => Yii::$app->user->identity->id
            ]
        )){
            return floatval($model->score);
        }else{
            return 0;
        }
    }

    public function getLastComments()
    {
        return Comment::find()
        ->where(
            [
                'tvshow' => $this->tvshow,
                'season_num' => $this->season_num,
                'episode_num' => $this->episode_num,
                'visible' => 1, 
                'answer_to' => null
            ])
        ->limit(3)->orderBy(['date'=>SORT_DESC])->all();
    }

    
    public function getComments()
    {
        // Method 1
        $response = [];
        $comments = Comment::find()->where(['tvshow' => $this->tvshow, 'season_num' => $this->season_num, 'episode_num' => $this->episode_num, 'visible' => 1, 'answer_to' => null])->all();
        
        foreach ($comments as $key => $value) {
            $comment = $value->toArray();
            $comment['answers'] = $value->getAnswers();
            array_push($response, $comment);
        }

        return $response;
    }
    
    public function getTMDbData(){
        return Yii::$app->TMDb->getEpisodeData($this->tvshowModel->title->id_tmdb, $this->season_num, $this->episode_num);
    }

    public function isReleased()
    {
        $release = strtotime($this->getTMDbData()['air_date']);
        $today = strtotime(date("Y-m-d"));
        if ($release > $today) {
            return false;
        }
        return true;
    }

    public function isWatched($tvshow = null, $season = null, $episode=null){
    if(WatchEpisode::findOne(
        [
                'tvshow'=>$this->tvshow,
                'season_num' => $this->season_num,
                'episode_num' => $this->episode_num,
                'user' => Yii::$app->user->identity->id
            ]
        )){
            return true;
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlatformLinks()
    {
        return (new \yii\db\Query())
            ->select(['`platform`.`name`', '`stores_episode`.`link`'])
            ->from('`stores_episode`, `platform`')
            ->where(
                '`stores_episode`.`tvshow` = :id AND'
                .' `stores_episode`.`season_num` = :season AND'
                .' `stores_episode`.`episode_num` = :episode AND'
                .' `platform`.`id` = `stores_episode`.`platform`'
            )
            
            // Avoid SQL Injections
            ->addParams([':id' => $this->tvshow, ':season'=>'season_num', ':episode'=>'episode_num'])  
            ->all();
    }

    public function needsUpdate(){
        $last_update = strtotime("$this->last_update ".Yii::$app->params['api']['v1']['timeToRefeshCache']);
        $today = strtotime(date("Y-m-d"));

        if (!$this->last_update || !$this->cache || $last_update <= $today) {
            return true;
        }
        return false;
    }

}
