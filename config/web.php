<?php

$params = require(__DIR__ . "/params.php");
$db = require(__DIR__ . "/db.php");

$config = [
    "id" => "basic",
    "basePath" => dirname(__DIR__),
    "bootstrap" => ["log"],
    'language' => 'zh-CN',
    "components" => [
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'zh-CN',
                    'fileMap' => [
                        'app' => 'yii.php',
                        'app/error' => 'error.php',
                    ],
                ],
            ],
        ],
        "request" => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            "cookieValidationKey" => "0Wm*3Me#W%QXIHYwb^$*zeA%kW4y8NBq",
            "enableCsrfValidation"=>false,
        ],
        "cache" => [
            "class" => "yii\caching\FileCache",
        ],
        "user" => [
            "identityClass" => "app\models\User",
            "enableAutoLogin" => true,
            "returnUrl" => "/admin/user",
            "loginUrl" => "/admin/site/login",
        ],
        "errorHandler" => [
            "errorAction" => "site/error",
        ],
        "mailer" => [
            "class" => "yii\swiftmailer\Mailer",
            // send all mails to a file by default. You have to set
            // "useFileTransport" to false and configure a transport
            // for the mailer to send real emails.
            "useFileTransport" => true,
        ],
        "log" => [
            "traceLevel" => YII_DEBUG ? 3 : 0,
            "targets" => [
                [
                    "class" => "yii\log\FileTarget",
                    "levels" => ["error", "warning", "info"],
                ],
                [
                    "class" => "yii\log\FileTarget",
                    "levels" => ["info", "error", "warning"],
                    "logVars" => [],
                    "categories" => ["HR"], // HR => HttpRequest
                    "logFile" => "@runtime/logs/HttpRequest/".date("Ymd").".log",
                    "maxFileSize" => 1024 * 1,  // Size in KiloBytes
                    "maxLogFiles" => 100  // How many files can create at most
                ],
                [
                    "class" => "yii\log\FileTarget",
                    "levels" => ["info", "error", "warning"],
                    "logVars" => [],
                    "categories" => ["WX"], // HR => HttpRequest
                    "logFile" => "@runtime/logs/WeChat/".date("Ymd").".log",
                    "maxFileSize" => 1024 * 1,  // Size in KiloBytes
                    "maxLogFiles" => 100  // How many files can create at most
                ]
            ]
        ],
        "db" => $db,
        "urlManager" => [
            "enablePrettyUrl" => true,
            "showScriptName" => false,
            "rules" => [
                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/game'],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/contest'],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/rule'],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/stage'],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/enrollment'],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/opponent'],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/opponent-stage'],
                "wechat\-api/<weChat:[\w|\d|_|-]+>" => "wechat-api/index",
                "active\-we\-api\/user\/<weChat:[\w|\d|_|-]+>\/<type:(snsapi_userinfo|snsapi_base)>" => "active-we-api/user",
                "active\-we\-api\/<action:[\w|-]+>\/<weChat:[\w|\d|_|-]+>" => "active-we-api/<action>"
            ],
        ]
    ],
    "params" => $params,
    "modules" => [
        "api" => [
            "class" => "app\modules\api\Module",
        ],
        "admin" =>[
            'class' => 'app\modules\admin\Admin',
        ],
        'gridview' => [
    'class' => '\kartik\grid\Module',
        ]
    ]
];

if (YII_ENV_DEV) {
    // configuration adjustments for "dev" environment
    $config["bootstrap"][] = "debug";
    $config["modules"]["debug"] = [
        "class" => "yii\debug\Module",
        // uncomment the following to add your IP if you are not connecting from localhost.
        //"allowedIPs" => ["127.0.0.1", "::1"],
    ];

    $config["bootstrap"][] = "gii";
    $config["modules"]["gii"] = [
        "class" => "yii\gii\Module",
        // uncomment the following to add your IP if you are not connecting from localhost.
        //"allowedIPs" => ["127.0.0.1", "::1"],
    ];
}

return $config;
