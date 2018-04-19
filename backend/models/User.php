<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\filters\auth\HttpBearerAuth;
use common\models\LoginForm;

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
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }


    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REGISTER = 'register';
    const SCENARIO_CREATE = 'create';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_LOGIN] = ['username', 'password'];
        $scenarios[self::SCENARIO_REGISTER] = ['username', 'email', 'password'];
        $scenarios[self::SCENARIO_CREATE] =
            ['id', 'username', 'email', 'password', 'name', 'state', 'created_at', 'birthdate', 'description', 'profile_img', 'background_img', 'country'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'username', 'email', 'password'], 'required'],
            [['state'], 'integer'],
            [['created_at', 'birthdate'], 'safe'],
            [['name'], 'string', 'max' => 30],
            [['username'], 'string', 'max' => 25],
            [['email', 'password', 'profile_img', 'background_img', 'password_reset_token'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 120],
            [['country'], 'string', 'max' => 60],
            [['tmdb_gtoken'], 'string', 'max' => 35],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['auth_key'], 'unique'],
            [['password_reset_token'], 'unique'],
            [['tmdb_gtoken'], 'unique'],
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

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'state' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'state' => self::STATUS_ACTIVE]);
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
            'state' => self::STATUS_ACTIVE,
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['author' => 'id']);
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
        return $this->hasMany(User::className(), ['id' => 'follower'])->viaTable('follow_usr', ['followed' => 'id']);
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
    public function getTvshows()
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
    public function getMovies()
    {
        return $this->hasMany(Movie::className(), ['id' => 'movie'])->viaTable('watch_movie', ['user' => 'id']);
    }
}
