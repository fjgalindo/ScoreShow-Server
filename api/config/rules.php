<?php
return [
    //'<alias:\w+>' => 'site/<alias>',
    '/' => 'v1/default/say-hello',
    [
        'class' => 'yii\web\GroupUrlRule',
        'prefix' => 'v1',
        'rules' => [

            /* === TVSHOW ROUNTING RULES === */
            'tv/premieres' => 'episode/premieres',
            'tv/recommendations' => 'tvshow/recommendations',
            'tv/popular' => 'tvshow/popular',
            'tv/top-rated' => 'tvshow/top-rated',
            'tv/<id:\d+>' => 'tvshow/view-model',
            'tv/<id:\d+>/follow' => 'tvshow/follow',
            'tv/<id:\d+>/unfollow' => 'tvshow/unfollow',
            'tv/<id:\d+>/comment' => 'tvshow/comment',
            'tv/<id:\d+>/comments' => 'tvshow/view-comments',
            'tv/<id:\d+>/last-comments' => 'tvshow/last-comments',

            /* === EPISODES ROUTING RULES === */
            'tv/<id:\d+>/season/<season:\d+>' => 'episode/list-season',
            'tv/<id:\d+>/season/<season:\d+>/watch' => 'episode/watch-season',
            'tv/<id:\d+>/season/<season:\d+>/unwatch' => 'episode/unwatch-season',
            'tv/<id:\d+>/season/<season:\d+>/episode/<ep:\d+>' => 'episode/view-model',
            'tv/<id:\d+>/season/<season:\d+>/episode/<ep:\d+>/watch' => 'episode/watch',
            'tv/<id:\d+>/season/<season:\d+>/episode/<ep:\d+>/unwatch' => 'episode/unwatch',
            'tv/<id:\d+>/season/<season:\d+>/episode/<ep:\d+>/score' => 'episode/score',
            'tv/<id:\d+>/season/<season:\d+>/episode/<episode:\d+>/comment' => 'episode/comment',
            'tv/<id:\d+>/season/<season:\d+>/episode/<episode:\d+>/comments' => 'episode/view-comments',
            'tv/<id:\d+>/season/<season:\d+>/episode/<episode:\d+>/last-comments' => 'episode/last-comments',

            'tv/get/<id_tmdb:\d+>' => 'tvshow/get', // Called on search from TMDb.

            /* === MOVIES ROUNTING RULES === */
            'movie/premieres' => 'movie/premieres',
            'movie/recommendations' => 'movie/recommendations',
            'movie/popular' => 'movie/popular',
            'movie/top-rated' => 'movie/top-rated',
            'movie/<id:\d+>' => 'movie/view-model',
            'movie/<id:\d+>/follow' => 'movie/follow',
            'movie/<id:\d+>/unfollow' => 'movie/unfollow',
            'movie/<id:\d+>/watch' => 'movie/watch',
            'movie/<id:\d+>/unwatch' => 'movie/unwatch',
            'movie/<id:\d+>/score' => 'movie/score',
            'movie/<id:\d+>/comment' => 'movie/comment',
            'movie/<id:\d+>/last-comments' => 'movie/last-comments',
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
            'user/<id:\d+>/activity' => 'user/activity',
            'user/find' => 'user/find-by-name',

            /* === AUTH USER ACTIONS === */
            'my' => 'user/profile',
            'my/update' => 'user/update-model',
            'my/upload-image' => 'user/upload-image',
            'my/comments' => 'user/my-comments',
            'my/stats' => 'user/my-stats',
            'my/followeds' => 'user/followeds',
            'my/activity' => 'user/activity',
            'my/premieres' => 'title/premieres',
            'my/followeds-activity' => 'user/followeds-activity',

            'register' => 'user/register',
            'login' => 'user/auth',

            'search' => 'default/search-tmdb',
            'image/tmdb' => 'default/image-tmdb',
            'image' => 'default/image',

            '/' => 'default/say-hello',
        ],
    ],
];
