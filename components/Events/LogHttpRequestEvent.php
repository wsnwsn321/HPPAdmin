<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 10/07/2017
 * Time: 6:56 PM
 */
namespace app\components\Events;

class LogHttpRequestEvent extends \yii\base\Event
{
    public $url;
    public $params;
    public $method;
    public $response;
    public $obj;
}