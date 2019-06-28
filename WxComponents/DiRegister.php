<?php
/* CaryYe 22/03/2018 3:34 PM */
namespace app\WxComponents;

/**
 * Class DiRegister
 * @package app\WxComponents
 */
class DiRegister extends \yii\base\Component
{
    /**
     * @param string $weChat
     * @return boolean
     */
    public static function exe($weChat)
    {
        try {
            \yii::$container->setSingleton("app\\WxComponents\\Instance",
                new \app\WxComponents\Instance(["weChatId" => trim($weChat)])
            );
        } catch (\Exception $e) {
            if ($e->getMessage() === "Null") {
                echo  "WeChat Id not found!";
            }
            return false;
        }

        // Set the shortcut.
        \yii::$container->setSingleton("weChat","app\\WxComponents\\Instance");
        return true;
    }
}