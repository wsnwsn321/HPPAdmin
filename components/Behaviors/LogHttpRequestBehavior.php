<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 10/07/2017
 * Time: 6:17 PM
 */
namespace app\components\Behaviors;

use yii\base\Behavior;

class LogHttpRequestBehavior extends Behavior
{
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            \app\components\HttpRequest::LOGGER => [$this, "logger"]
        ];
    }

    /**
     * @param \yii\base\Event $event
     * @return void
     */
    public function logger($event) {
        // curl information
        $info = curl_getinfo($event->obj);
        $info = [
            "url" => $info["url"],
            "http_code" => $info["http_code"],
            "total_time" => $info["total_time"]
        ];

        // Logger
        \yii::info([
            "url" => $event->url,
            "params" => $event->params,
            "method" => $event->method,
            "response" => $event->response,
            "escaped" => $info
        ], "HR");
    }
}