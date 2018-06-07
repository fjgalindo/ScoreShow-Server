<?php

return [
    [['name', 'username', 'email', 'password'], 'required'],
    [['state'], 'integer'],
    [['created_at', 'birthdate'], 'safe'],
    [['name'], 'string', 'max' => 60],
    [['username'], 'string', 'max' => 25],
    [['email', 'password', 'profile_img', 'background_img', 'password_reset_token'], 'string', 'max' => 255],
    [['auth_key'], 'string', 'max' => 32],
    [['description'], 'string', 'max' => 120],
    [['country'], 'string', 'max' => 60],
    [['tmdb_gtoken'], 'string', 'max' => 35],
    [['username'], 'unique', 'message' => "El nombre de usuario ya esta siendo utilizado."],
    [['email'], 'unique', 'message' => "El email ya esta siendo utilizado."],
    [['auth_key'], 'unique'],
    [['password_reset_token'], 'unique'],
    [['tmdb_gtoken'], 'unique'],
    ['state', 'default', 'value' => self::STATUS_ACTIVE],
    ['state', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
    [['activity'], 'safe'],
];
