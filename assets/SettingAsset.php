<?php
/** CaryYe 2019/7/10 8:00 AM */
namespace app\assets;

use yii\web\AssetBundle;

class SettingAsset extends AssetBundle
{
    public $sourcePath = "@app/web/js";

    public $js = ["setting.js"];

    public $depends = ["yii\web\JqueryAsset"];

    public $jsOptions = [
        "position" => \yii\web\View::POS_HEAD,   // 这是设置所有js放置的位置
    ];
}