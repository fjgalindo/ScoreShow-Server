<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'scoreshow-api',
    'basePath' => dirname(__DIR__),
    'name' => 'ScoreShow API',
    'language' => 'es-ES',
    'bootstrap' => ['log'],
    //'catchAll' => ['site/offline'],
    'modules' => [
        'v1' => [
            'basePath' => '@app/modules/v1',
            'class' => 'api\modules\v1\Module',
        ],
    ],
    'components' => [
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'formatters' => [
                \yii\web\Response::FORMAT_JSON => [
                    'class' => 'yii\web\JsonResponseFormatter',
                ],
            ],

                /*
            'on beforeSend' => function ($event) {
            $response = $event->sender;
            if ($response->data !== null //&& Yii::$app->request->get('suppress_response_code')
            ) {
            $response->data = [
            'success' => $response->isSuccessful,
            'data' => $response->data,
            ];
            //$response->statusCode = 200;
            }
            },*/
        ],
        'user' => [
            'identityClass' => 'api\modules\v1\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'normalizer' => [
                'class' => 'yii\web\UrlNormalizer',
                // use temporary redirection instead of permanent for debugging
                //'action' => yii\web\UrlNormalizer::ACTION_REDIRECT_TEMPORARY,
            ],
            'rules' => include 'rules.php',
        ],

    ],
    'params' => $params,
];
