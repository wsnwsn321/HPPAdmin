<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Songnan Wu <qiang.xue@gmail.com>
 * @since 2019/06/14
 */
class DeleteAllAsset extends AssetBundle
{
    public $sourcePath = '@bower/customize/js';

    public $js = ['firmusers.js'];

    public $depends = ['yii\web\JqueryAsset'];
}
