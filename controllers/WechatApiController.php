<?php
/* CaryYe 21/03/2018 5:00 PM */
namespace app\controllers;
use app\WxComponents\DiRegister;
use app\WxComponents\Tools;

/**
 * Class WapArrangeController
 * @package app\controllers
 */
class WechatApiController extends \yii\web\Controller
{
    /** @inheritdoc */
    public $enableCsrfValidation = false;

    /**
     * Verify WeChat accounts.
     * @return string
     */
    public function actionIndex()
    {
        if (! DiRegister::exe(trim(\yii::$app->request->get("weChat")))) {
            return "";
        }

        if (! $this->verify()) {
            return "Verify failed!";
        }

        if (!is_null(\yii::$app->request->get("echostr"))) {
            return \yii::$app->request->get("echostr");
        }

        $body = Tools::xml2arr(trim(file_get_contents("php://input")));

        if (isset($body["MsgType"])
            && trim($body["MsgType"]) !== '')
        {
            return $this->router($body);
        }

        return "";
    }

    /**
     * @param array $msg
     * @return mixed
     */
    private function router($msg)
    {
        $namespace = "app\\WxComponents\\";

        switch ($msg["MsgType"]) {
            case "text":
            case "event":
                $type = trim($msg["MsgType"]);
                $launchName = $type == "event" ? $msg["Event"] : $type;
                $class = $namespace.$type."\\".ucfirst($launchName);
                if (class_exists($class)) {
                    $obj = \yii::$container->get($class, [], ["data" => $msg]);
                    return $obj->exe();
                }
            break;
            default:
            break;
        }
        return "";
    }

    /** @return string */
    private function verify()
    {
        $obj = \yii::$container->get("\\app\\WxComponents\\Signature");
        $signature = $obj->verify(
            \yii::$app->request->get("signature", ""),
            \yii::$app->request->get("timestamp", ""),
            \yii::$app->request->get("nonce", "")
        );
        unset($obj);

        return $signature;
    }
}