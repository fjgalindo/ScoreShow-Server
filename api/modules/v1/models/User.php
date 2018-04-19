<?php

namespace api\modules\v1\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string $password
 * @property int $state
 * @property string $auth_key
 * @property string $created_at
 * @property string $description
 * @property string $birthdate
 * @property string $profile_img
 * @property string $background_img
 * @property string $country
 * @property string $password_reset_token
 * @property string $tmdb_gtoken
 *
 * @property Comment[] $comments
 * @property FollowTitle[] $followTitles
 * @property Title[] $titles
 * @property FollowUsr[] $followUsrs
 * @property FollowUsr[] $followUsrs0
 * @property User[] $followers
 * @property User[] $followeds
 * @property Report[] $reports
 * @property WatchEpisode[] $watchEpisodes
 * @property Episode[] $tvshows
 * @property WatchMovie[] $watchMovies
 * @property Movie[] $movies
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{

    public $activity;
    public $repeat_password;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return include 'rules/UserRules.php';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'username' => Yii::t('app', 'Username'),
            'email' => Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
            'state' => Yii::t('app', 'State'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'created_at' => Yii::t('app', 'Created At'),
            'description' => Yii::t('app', 'Description'),
            'birthdate' => Yii::t('app', 'Birthdate'),
            'profile_img' => Yii::t('app', 'Profile Img'),
            'background_img' => Yii::t('app', 'Background Img'),
            'country' => Yii::t('app', 'Country'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'tmdb_gtoken' => Yii::t('app', 'Tmdb Gtoken'),
        ];
    }

    const SCENARIO_DEFAULT = 'default';
    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REGISTER = 'register';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_CREATE = 'create';
    const SCENARIO_VIEW = 'view';
    const SCENARIO_UPLOAD_IMG = 'upload_profile_img';

    public function scenarios()
    {
        return [
            parent::scenarios(),
            self::SCENARIO_DEFAULT =>
            ['name', 'username', 'status', 'auth_key', 'email', 'password', 'description', 'birthdate', 'country', 'created_at', 'tmdb_gtoken'],
            self::SCENARIO_LOGIN => ['username', 'password'],
            self::SCENARIO_REGISTER => ['username', 'email', 'password', 'name'],
            self::SCENARIO_CREATE =>
            ['name', 'username', 'status', 'auth_key', 'email', 'password', 'description', 'birthdate', 'country', 'created_at', 'tmdb_gtoken'],
            self::SCENARIO_VIEW =>
            ['name', 'username', 'email', 'description', 'birthdate', 'country'],
            self::SCENARIO_UPDATE =>
            ['name', 'email', 'password', 'repeat_password', 'description', 'birthdate', 'country', 'updated_at', 'tmdb_gtoken'],
            self::SCENARIO_UPLOAD_IMG =>
            ['profile_img', 'background_img'],
        ];
    }

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;


    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }




    public function setActivity($activity){
        $this->activity = $activity;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        $response = [];
        $response['comments'] = $this->hasMany(Comment::className(), ['author' => 'id'])->where(['visible' => 1, 'answer_to' => null])->orderBy('date')->all();
        $response['answers'] = $this->hasMany(Comment::className(), ['author' => 'id'])->where(['visible' => 1])->andWhere(['not', ['answer_to' => null]])->orderBy('date')->all();

        return $response;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollowTitles()
    {
        return $this->hasMany(FollowTitle::className(), ['user' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTitles()
    {
        return $this->hasMany(Title::className(), ['id' => 'title'])->viaTable('follow_title', ['user' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollowedTvshows()
    {
        return $this->hasMany(Title::className(), ['id' => 'title'])->where('`id` IN (SELECT id FROM tvshow)')->viaTable('follow_title', ['user' => 'id']);
        /*return Title::find()
    ->where("`title`.`id` IN (select `id` from `tvshow`) AND `title`.`id` IN (SELECT `id` FROM `follow_title`) AND $this->id IN (SELECT `id` FROM `follow_title`)")
    ;*/

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollowedMovies()
    {
        return $this->hasMany(Movie::className(), ['id' => 'title'])->viaTable('follow_title', ['user' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollowUsrs()
    {
        return $this->hasMany(FollowUsr::className(), ['followed' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollowUsrs0()
    {
        return $this->hasMany(FollowUsr::className(), ['follower' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollowers()
    {
        return $this->hasMany(User::className(), ['id' => 'follower'])->viaTable('follow_us', ['followed' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFolloweds()
    {
        return $this->hasMany(User::className(), ['id' => 'followed'])->viaTable('follow_usr', ['follower' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReports()
    {
        return $this->hasMany(Report::className(), ['author' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWatchEpisodes()
    {
        return $this->hasMany(WatchEpisode::className(), ['user' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWatchedEpisodes()
    {
        return $this->hasMany(Episode::className(), ['tvshow' => 'tvshow', 'season_num' => 'season_num', 'episode_num' => 'episode_num'])->viaTable('watch_episode', ['user' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWatchMovies()
    {
        return $this->hasMany(WatchMovie::className(), ['user' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWatchedMovies()
    {
        return $this->hasMany(Movie::className(), ['id' => 'movie'])->viaTable('watch_movie', ['user' => 'id']);
    }

    public function getUserActivity()
    {
        $activity = [];
        $activity['shows'] = [];

        $activity['shows']['following'] = [];

        $activity['shows']['following']['tvshows'] = $this->followedTvshows;
        $activity['shows']['following']['movies'] = $this->followedMovies;

        $activity['shows']['watched'] = [];
        $activity['shows']['watched']['movies'] = $this->watchedMovies;
        $activity['shows']['watched']['episodes'] = $this->watchedEpisodes;

        $activity['comments'] = $this->comments;

        $activity['follows_users'] = $this->followeds;
        return $activity;
    }

    public function validateModifiedPassword()
    {
        if ($this->password === $this->repeat_password) {
            return true;
        }
        $this->addError('password', "Las contraseñas deben coincidir");
        $this->addError('repeat_password', "Las contraseñas deben coincidir");
        return false;
    }

}
