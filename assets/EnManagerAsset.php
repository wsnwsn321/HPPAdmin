<?php
/** CaryYe 2019/6/27 7:29 AM */
namespace app\assets;

use yii\web\AssetBundle;

class EnManagerAsset extends AssetBundle
{
    public $sourcePath = '@app/web/js';

    public $js = ['en-manager.js'];

    public $depends = ['yii\web\JqueryAsset'];
}
