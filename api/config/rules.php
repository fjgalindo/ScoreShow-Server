<?php
return [
    //'<alias:\w+>' => 'site/<alias>',
    '/' => 'default/index',
    [
        'class' => 'yii\web\GroupUrlRule',
        'prefix' => 'v1',
        'rules' => [

            /* === TVSHOW ROUNTING RULES === */
            'tv/' => 'tvshow/index',
            'tv/pending' => 'tvshow/to-watch',
            'tv/<id:\d+>' => 'tvshow/view-model',
            'tv/<id:\d+>/follow' => 'tvshow/follow',
            'tv/<id:\d+>/unfollow' => 'tvshow/unfollow',
            'tv/<id:\d+>/comment' => 'tvshow/comment',
            'tv/<id:\d+>/comments' => 'tvshow/view-comments',

            /* === EPISODES ROUTING RULES === */
            'tv/<id:\d+>/season/<season:\d+>' => 'episode/list-season',
            'tv/<id:\d+>/season/<season:\d+>/watch' => 'episode/watch-season',
            'tv/<id:\d+>/season/<season:\d+>/unwatch' => 'episode/unwatch-season',
            'tv/<id:\d+>/season/<season:\d+>/episode/<ep:\d+>' => 'episode/view-model',
            'tv/<id:\d+>/season/<season:\d+>/episode/<ep:\d+>/last-comments' => 'episode/last-comments',
            'tv/<id:\d+>/season/<season:\d+>/episode/<ep:\d+>/platforms' => 'episode/platforms',
            'tv/<id:\d+>/season/<season:\d+>/episode/<ep:\d+>/watch' => 'watch-episode/watch',
            'tv/<id:\d+>/season/<season:\d+>/episode/<ep:\d+>/unwatch' => 'watch-episode/unwatch',
            'tv/<id:\d+>/season/<season:\d+>/episode/<ep:\d+>/score' => 'episode/score',
            'tv/<id:\d+>/season/<season:\d+>/episode/<episode:\d+>/comment' => 'episode/comment',
            'tv/<id:\d+>/season/<season:\d+>/episode/<episode:\d+>/comments' => 'episode/view-comments',

            'tv/get/<id_tmdb:\d+>' => 'tvshow/get', // Called on search from TMDb.

            /* === MOVIES ROUNTING RULES === */
            //'movie/' => 'movie/index',
            'movie/pending' => 'movie/to-watch',
            'movie/platforms' => 'movie/platforms',
            'movie/last-comments' => 'movie/last-comments',
            'movie/<id:\d+>' => 'movie/view-model',
            'movie/<id:\d+>/follow' => 'movie/follow',
            'movie/<id:\d+>/unfollow' => 'movie/unfollow',
            'movie/<id:\d+>/watch' => 'movie/watch',
            'movie/<id:\d+>/unwatch' => 'movie/unwatch',
            'movie/<id:\d+>/score' => 'movie/score',
            'movie/<id:\d+>/comment' => 'movie/comment',
            'movie/<id:\d+>/comments' => 'movie/view-comments',

            'movie/get/<id_tmdb:\d+>' => 'movie/get', // Called on search from tmdb (from tmdb search component)

            /* == COMMENT ROUTING RULES == */
            'comment/<id:\d+>' => 'comment/view',
            'comment/<id:\d+>/delete' => 'comment/delete',
            'comment/<id:\d+>/answer' => 'comment/answer',

            /* === USER ROUNTING RULES === */
            'user/<id:\d+>' => 'user/view-model',
            'user/<id:\d+>/follow' => 'user/follow-user',
            'user/<id:\d+>/unfollow' => 'user/unfollow-user',

            /* === AUTH USER ACTIONS === */
            'my' => 'user/profile',
            'my/update' => 'user/update',
            'my/comments' => 'user/my-comments',
            'my/stats' => 'user/my-stats',

            'register' => 'user/register',
            'login' => 'user/auth',

            'search' => 'default/search-tmdb',

            '/' => 'default/index',
        ],
    ],
];
