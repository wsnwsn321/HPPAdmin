<?php
/* CaryYe 2018/4/12 9:41 AM */
namespace app\controllers\ActiveWeApis;
use app\models\wx\Menu;

/**
 * Class MenuAction
 * @package app\controllers\ActiveWeApis
 */
class MenuAction extends \yii\base\Action
{
    /**
     * Runs the Action.
     * @return string
     */
    public function run()
    {
        $menu = new Menu(["pubArg" => [
            "php7Login_firm" => (int) \yii::$app->request->get("firm", 0)
        ]]);
        $r = $menu->create();
        return $r
            ? json_encode(["code" => 1, "message" => "success"])
            : json_encode(["code" => 0, "message" => "failed"]);
    }
}