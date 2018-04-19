<?php
return [
    //'<alias:\w+>' => 'site/<alias>',
    '/' => 'site/index',
    '<controller:(user|platform|comment)>s/create' => '<controller>/create',
    '<controller:(user|platform|comment)>s/<id:\d+>/<action:(update|delete)>' => '<controller>/<action>',
    '<controller:(user|platform|comment)>s/<id:\d+>' => '<controller>/view',
    //'<controller:(usuario|plataforma|comentario)>s' => '<controller>/index',
    ['class' => 'yii\rest\UrlRule',
        'pluralize' => false,
        'controller' => ['site', 'login',],
    ],
    ['class' => 'yii\rest\UrlRule',
        'pluralize' => false,
        'controller' => ['tvshow', 'movie', 'user'],
        
        //'extraPatterns' => ['POST authenticate' => 'authenticate'],
    ],
];
