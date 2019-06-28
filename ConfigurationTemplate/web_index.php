<?php
/** CaryYe at 02/11/2017 9:45 AM */
// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', @YII_DEBUG.open@);
defined('YII_ENV') or define('YII_ENV', '@YII_ENV.level@');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
