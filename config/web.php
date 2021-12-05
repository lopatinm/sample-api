<?php
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
    ],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'request' => [
            'cookieValidationKey' => 'Ex01Ga0qeGfFn_htk2FD8O7mDlI6gD1P',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'format' => \yii\web\Response::FORMAT_JSON,
            'on beforeSend' => function (yii\base\Event $event) {
                $response = $event->sender;
                if ((409 === $response->statusCode || 400 === $response->statusCode || 204 === $response->statusCode || 500 === $response->statusCode || 404 === $response->statusCode || 401 === $response->statusCode || 403 === $response->statusCode) && is_array($response->data)) {
                    if($response->data['code'] == 485){
                        $response->data['name'] = "Already exist";
                    }
                    if($response->statusCode == 404){
                        $headers = Yii::$app->request->headers;
                        $accept = $headers->get('Accept');
                        if($accept == "application/json;" || $accept == "*/*"){
                            $response->format = \yii\web\Response::FORMAT_JSON;
                        }else{
                            $response->format = \yii\web\Response::FORMAT_XML;
                        }
                    }
                    if($response->statusCode == 500){
                        unset($response->data['file']);
                        unset($response->data['line']);
                        unset($response->data['stack-trace']);
                    }
                    $response->data['code'] = $response->statusCode;
                    $response->data['status'] = 'Error';
                    unset($response->data['type']);
                }
            },

        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\modules\v1\models\User',
            'enableAutoLogin' => false,
            'loginUrl' => null,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
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
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => [
                        'v1/user'
                    ],
                    'extraPatterns' => [
                        'POST login' => 'login',
                        'OPTIONS login' => 'options',
                        'POST registration' => 'registration',
                        'OPTIONS registration' => 'options'
                    ]
                ],
            ],
        ],

    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];
}

return $config;
