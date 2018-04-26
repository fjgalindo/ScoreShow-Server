CREATE DATABASE IF NOT EXISTS scoreshow
	DEFAULT CHARACTER SET utf8
	DEFAULT COLLATE utf8_spanish_ci;

	/*
		Tables definition for ScoreShow Database
	*/

USE scoreshow;

CREATE TABLE IF NOT EXISTS `user` (
	id INT(8) PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(60) NOT NULL,
	username VARCHAR (25) NOT NULL UNIQUE,
	email VARCHAR(255) NOT NULL UNIQUE,
	password VARCHAR (255) NOT NULL,
	status INT(1) NOT NULL DEFAULT 0,
	#token VARCHAR(255) UNIQUE,
	auth_key VARCHAR(255) NOT NULL UNIQUE,
	created_at DATETIME,
	updated_at DATETIME,
	description VARCHAR (120),
	birthdate DATE,
	profile_img VARCHAR(255),
	background_img VARCHAR(255),
	country VARCHAR(60),
	password_reset_token VARCHAR(255) UNIQUE,
	tmdb_gtoken VARCHAR (35) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS `notification` (
	id INT PRIMARY KEY AUTO_INCREMENT,
	content VARCHAR(255) NOT NULL,
	link VARCHAR(350),
	`date` DATETIME,
	seen BOOLEAN DEFAULT 0
);

CREATE TABLE IF NOT EXISTS `follow_usr` (
	follower INT(8),
	followed INT(8),
	accepted BOOLEAN NOT NULL DEFAULT 0,

	CONSTRAINT PK_followUsr PRIMARY KEY (follower, followed),
	CONSTRAINT FK_followUsr_Follower
		FOREIGN KEY (follower) REFERENCES user(id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	CONSTRAINT FK_followUsr_Followed 
		FOREIGN KEY (followed) REFERENCES user(id)
		ON DELETE CASCADE
		ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `title` (
	id INT(10) PRIMARY KEY AUTO_INCREMENT,
	id_tmdb INT NOT NULL,
	cache MEDIUMTEXT,
	last_update DATETIME
);

CREATE TABLE IF NOT EXISTS `follow_title` (
	user INT(8),
	title INT(10),
	`date` DATE,

	CONSTRAINT PK_followtitle PRIMARY KEY (title, user),
	CONSTRAINT FK_followtitle_title 
		FOREIGN KEY (title) REFERENCES title(id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	CONSTRAINT FK_followtitle_user
		FOREIGN KEY (user) REFERENCES user(id)
		ON DELETE CASCADE
		ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `tvshow` (
	id INT(10) PRIMARY KEY,

	CONSTRAINT FK_tvshow_title 
		FOREIGN KEY (id) REFERENCES title(id)
		ON DELETE CASCADE
		ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `episode` (
	tvshow INT(10),
	season_num INT(3),
	episode_num INT(3),
	cache MEDIUMTEXT,
	last_update DATETIME,

	CONSTRAINT PK_episode 
		PRIMARY KEY (tvshow, season_num, episode_num),
	CONSTRAINT FK_episode_tvshow
		FOREIGN KEY (tvshow) REFERENCES tvshow(id)
		ON DELETE CASCADE
		ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `movie` (
	id INT(10) PRIMARY KEY,

	CONSTRAINT FK_movie_title 
		FOREIGN KEY (id) REFERENCES title(id)
		ON DELETE CASCADE
		ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `watch_episode` (
	user INT(8),
	tvshow INT(10),
	season_num INT(3),
	episode_num INT(3),
	`date` DATETIME,
	score DECIMAL(3,1),

	CONSTRAINT PK_watchepisode PRIMARY KEY (user, tvshow, season_num, episode_num),
	CONSTRAINT FK_watchepisode_user 
		FOREIGN KEY (user) REFERENCES user(id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	CONSTRAINT FK_watchepisode_episode 
		FOREIGN KEY (tvshow, season_num, episode_num) REFERENCES episode(tvshow, season_num, episode_num)
		ON DELETE CASCADE
		ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `watch_movie` (
	user INT(8),
	movie INT(10),
	`date` DATETIME,
	score DECIMAL(3,1),

	CONSTRAINT PK_watchmovie PRIMARY KEY (user, movie),
	CONSTRAINT FK_watchmovie_user 
		FOREIGN KEY (user) REFERENCES user(id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	CONSTRAINT FK_watchmovie_movie 
		FOREIGN KEY (movie) REFERENCES movie(id)
		ON DELETE CASCADE
		ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `comment` (
	id INT(15) PRIMARY KEY AUTO_INCREMENT,
	author INT(8),
	title INT(10),
	tvshow INT(10),
	season_num INT(3),
	episode_num INT(3),
	`date` DATETIME NOT NULL,
	answer_to INT(15),
	content VARCHAR(300) NOT NULL,
	visible BOOLEAN NOT NULL,
	
	CONSTRAINT FK_comment_author 
		FOREIGN KEY (author) REFERENCES user(id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	CONSTRAINT FK_comment_answerTo
		FOREIGN KEY (answer_to) REFERENCES comment(id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	CONSTRAINT FK_comment_title
		FOREIGN KEY (title) REFERENCES title(id)
		ON DELETE CASCADE 
		ON UPDATE CASCADE,
	CONSTRAINT FK_comment_episode
		FOREIGN KEY (tvshow, season_num, episode_num) REFERENCES episode(tvshow, season_num, episode_num)
		ON DELETE CASCADE
		ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `report` (
	id INT(10) PRIMARY KEY AUTO_INCREMENT,
	author INT(8) NOT NULL,
	comment INT(15),
	`date` DATETIME,
	reason VARCHAR(500) NOT NULL,

	CONSTRAINT FK_report_author 
		FOREIGN KEY (author) REFERENCES user(id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	CONSTRAINT FK_report_comment 
		FOREIGN KEY (comment) REFERENCES comment(id) 
		ON DELETE CASCADE
		ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `platform` (
	id INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR (30) NOT NULL,
	logo VARCHAR(100),
	website VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS `stores_episode` (
	platform INT,
	tvshow INT(10),
	season_num INT(3),
	episode_num INT(3),
	link VARCHAR (150),

	CONSTRAINT PK_storesepisode
		PRIMARY KEY (platform, tvshow, season_num, episode_num),
	CONSTRAINT FK_storesepisode_platform 
		FOREIGN KEY (platform) REFERENCES platform(id) 
		ON DELETE CASCADE 
		ON UPDATE CASCADE, 
	CONSTRAINT FK_storesepisode_episode
		FOREIGN KEY (tvshow, season_num, episode_num) REFERENCES episode(tvshow, season_num, episode_num) 
		ON DELETE CASCADE 
		ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `stores_title` (
	platform INT,
	title INT(10),
	link VARCHAR(150),

	CONSTRAINT PK_storestitle PRIMARY KEY (platform, title),
	CONSTRAINT FK_storestitle_platform 
		FOREIGN KEY (platform) REFERENCES platform(id) 
		ON DELETE CASCADE 
		ON UPDATE CASCADE, 
	CONSTRAINT FK_storestitle_title 
		FOREIGN KEY (title) REFERENCES title(id) 
		ON DELETE CASCADE 
		ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `superusers` (
	id INT PRIMARY KEY,
	username VARCHAR(20) UNIQUE,
	name VARCHAR(120),
	email VARCHAR(50) UNIQUE,
	password VARCHAR(128),
	created_at DATETIME
);
