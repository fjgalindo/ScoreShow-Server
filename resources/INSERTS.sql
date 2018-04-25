USE `scoreshow`;

INSERT IGNORE INTO `user` (username, email, `password`, name, `status`, description, created_at, auth_key, tmdb_gtoken) VALUES
	('pepe', 'pepeperez0123@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Pepe Pérez', 1, 'Aficionado al terror', '2018-03-20 00:00:00', 'uTxQHsnjtxHTzyyqJD7Vcyk77JK1OKU3SoPdul_s014BhwX76ZdFuS4P1Nfg7mmd', '6274b1be22582b93aa7dbac0e05d4c0a'),
	('pepe2', 'pepe2perez0123@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Pepe Pérez 2', 1, 'Aficionado a las serie', '2018-03-20 00:00:00', 'yRdJKaxycXXSl-fDzYs3jfLAFGOPkgcdIx92Hul9jiGSQgugSrDz-F9H9ZNQb44l', '7442f254d0a9fe67c5b40f35b973956b'),
	('javi', 'jotajotag0123@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Javi Javito', 1, 'Para mis ojos lo mejor, sino no lo quiero', '2018-03-20 00:00:00', 'IXwIHTmxmbrbI1hrz9iBBjHpaN5ReEnwiEr0RAMoO06qucAk4fEAfhG2TG3P2_42', '4c99a4c99eebb52905d1d7f3680b5ae4'),
	('sesi', 'sesisixto0123@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Cecilio Sexto', 1, 'Series y películas por doquier, dedicación en cuerpo y alma', '2018-03-20 00:00:00', 'DzbqumIGiGOwY6pp3CPnQn5-D0nRxzJ47hLeUO5bemXl_PclhFC6wGBSoBIiVxqA', 'f76b4d3806a5b6f8c53347bc374a4d55')
	;

INSERT IGNORE INTO `user` (`name`, `username`, `email`, `password`, `status`, `auth_key`, `created_at`, `updated_at`, `description`, `birthdate`, `profile_img`, `background_img`, `country`, `password_reset_token`, `tmdb_gtoken`) 
	VALUES ('Francis Jones', 'fjones', 'francisjones9192@gmail.com', '$2y$13$zbxoepOxfExF..bP3v3kh.PEhV9oxhLygi8TEsrOijL8wwe3Y2soO', '0', 'g3VELE7o-h0F-lsMhsGhYJuUhWqhvUl3', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, 'b3dbda9a477aa9edc2226c2592d1c2ce');

INSERT IGNORE INTO follow_usr (follower, followed) VALUES
	(1,2),
	(2,1),
	(1,3),
	(3,1),
	(4,3),
	(3,4),
	(2,4),
	(4,2)
	;

INSERT IGNORE INTO title (id, id_tmdb) VALUES
	(1, 1402),	/* TWD */
	(2, 44217), /* Vikings */
	(3, 1403), /* Agents of SHIELD */
	(4, 456), /* The Simpsons */
	(5, 1418), /* Big Bang Theory */

	(6, 637), /* La vida es bella */
	(7, 155), /* The Dark Knight */
	(8, 354912), /* Coco */
	(9, 19995), /* Avatar */
	(10, 76600); /* Avatar 2 */

INSERT IGNORE INTO `tvshow` (id) VALUES 
	(1),
	(2),
	(3),
	(4),
	(5);

INSERT IGNORE INTO `episode` (tvshow, season_num, episode_num) VALUES 
	(1, 8, 1),
	(1, 8, 2),
	(1, 8, 3),
	(1, 8, 4),
	(1, 8, 5),
	(1, 8, 6),
	(1, 8, 7),
	(1, 8, 8),
	(1, 8, 9),
	(1, 8, 10),
	(1, 8, 11),
	(1, 8, 12),
	(1, 8, 13),
	(1, 8, 14)
;


INSERT IGNORE INTO `movie` (id) VALUES 
	(6),
	(7),
	(8),
	(9),
	(10);


INSERT IGNORE INTO `platform` (id, name, logo, website) VALUES 
	(1, 'Netflix', '', ''),
	(2, 'HBO', '', ''),
	(3, 'Amazon Prime Video', '', ''),
	(4, 'Sky', '', '')
	;

INSERT IGNORE INTO `stores_title` (title, platform, link) VALUES
	(6, 1, 'https://www.netflix.com/es/title/1192333'),
	(7, 1, 'https://www.netflix.com/es/title/70079583'),
	(2, 1, 'https://www.netflix.com/es/title/70301870'),
	(2, 2, 'https://es.hboespana.com/series/vikings/00deea01-4f89-41ae-b459-ba33f17ff30d')
	;
/*
INSERT IGNORE INTO `stores_episode` (platform, tvshow, season_num, episode_num, link) VALUES 
	();
*/
INSERT IGNORE INTO `follow_title` (user, title) VALUES
	(1, 1),
	(1, 2),
	(1, 6),
	(1, 3),
	(1, 10),

	(2, 2),
	(2, 4),
	(2, 9),
	(2, 5),

	(3, 2),
	(3, 10),
	(3, 6),
	(3, 4),
	(3, 1),

	(4, 2),
	(4, 5),
	(4, 1),
	(4, 10),
	(4, 9)
	;

INSERT IGNORE INTO `watch_movie`(user, movie, `date`, score) VALUES
	(1, 6, '2018-03-20 00:00:00', 7.6),
	(1, 7, '2018-03-20 00:00:00', 9.0),
	(1, 8, '2018-03-20 00:00:00', 5.1),

	(3, 6, '2018-03-20 00:00:00', 8.2),
	(3, 7, '2018-03-20 00:00:00', 10.0),
	(3, 8, '2018-03-20 00:00:00', 9.0),
	(3, 9, '2018-03-20 00:00:00', 9.8),

	(4, 9, '2018-03-20 00:00:00', 6.8),
	(4, 6, '2018-03-20 00:00:00', 5.4),
	(4, 7, '2018-03-20 00:00:00', 10.0)
;


INSERT IGNORE INTO `comment` (author, title, `date`, answer_to, content, visible) VALUES
	/* ON TVSHOWS */
	(1, 1, '2018-03-20 00:00:00', NULL, 'Gran serie, pero no me gusta el rumbo que esta tomando ultimamente', 1),
		(2, 1, '2018-03-20 00:00:00', 1, 'Completamente de acuerdo contigo', 1),
		(3, 1, '2018-03-20 00:00:00', 1, 'Menos mal que de la próxima temporada se encarga otro', 1),
	(4, 1, '2018-03-20 00:00:00', NULL, 'Queremos más temporadas, esta serie mola mazo!!!', 1),
		(2, 1, '2018-03-20 00:00:00', 4, 'Donde hay que firmar?!', 1),
	
	/* ON MOVIES */
	(3, 7, '2018-03-20 00:00:00', NULL, 'Una obra maestra', 1),
		(2, 7, '2018-03-20 00:00:00', 6, 'Vete al carajo', 0),
		(2, 7, '2018-03-20 00:00:00', 6, 'Se merece el oscar a mejor director!', 1),
	(1, 7, '2018-03-20 00:00:00', NULL, 'Esperando a la próxima pelicula!', 1)

;

INSERT IGNORE INTO `comment` (author, tvshow, season_num, episode_num, `date`, answer_to, content, visible) VALUES
	/* ON EPISODES */
	(1, 1, 8, 1, '2018-03-20 00:00:00', NULL, 'LOL Rick se hizo viejo', 0),
		(2, 1, 8, 1, '2018-03-20 00:00:00', 8, 'Calla hombre no hagas spoiler!', 1),
	(2, 1, 8, 1, '2018-03-20 00:00:00', NULL, '¡Gran arranque de la temporada!', 1),
	(3, 1, 8, 1, '2018-03-20 00:00:00', NULL, 'Parece que vuelve la acción!', 1),
		(1, 1, 8, 1, '2018-03-20 00:00:00', 11, 'Si pues esperate a ver el resto de episodios, ¡la misma basura de siempre!', 0),
		(2, 1, 8, 1, '2018-03-20 00:00:00', 11, 'HOOYAH!', 1),
	(4, 1, 8, 1, '2018-03-20 00:00:00', NULL, 'Madremía 8 temporadas ya, esto se nos va de las manos...', 1)
;

INSERT IGNORE INTO `report` (author, comment, `date`, reason) VALUES
	(4, 5, '2018-03-20 00:00:00', "El usuario ha escrito contenido inapropiado."),
	(3, 5, '2018-03-20 00:00:00', "Este usuario me ha ofendido profundamente.")
;

