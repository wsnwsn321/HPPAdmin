<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 10/07/2017
 * Time: 2:09 PM
 */
namespace app\components;

use app\components\Behaviors\LogHttpRequestBehavior;

class HttpRequest extends \yii\base\Component
{
    const LOGGER = "logger";

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            "logger" => ["class" => LogHttpRequestBehavior::className()]
        ];
    }

    /**
     * @param string $url
     * @param array $params
     * @param string $method
     * @param bool $multi
     * @param array $extheaders
     * @return mixed
     */
    public function send($url, $params = array(), $method = "GET", $extheaders = array(), $multi = false)
    {
        !function_exists('curl_init') && exit('Need to open the curl extension');
        $method = strtoupper(trim($method));
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_USERAGENT, "PHP-SDK");
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ci, CURLOPT_TIMEOUT, 10);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, false);

        $headers = (array)$extheaders;
        switch ($method) {
            case "PUT":
            case "POST":
                if ($method == "PUT") {
                    curl_setopt($ci, CURLOPT_CUSTOMREQUEST, "PUT");
                    curl_setopt($ci, CURLOPT_HTTPHEADER, array("X-HTTP-Method-Override: PUT"));
                } else {
                    curl_setopt($ci, CURLOPT_POST, TRUE);
                }

                if (!empty($params)) {
                    if ($multi) {
                        foreach ($multi as $key => $file) {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    } else {
                        is_array($params) && $params = http_build_query($params);
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                    }
                }
                break;
            case "DELETE":
            case "GET":
                $method == "DELETE" && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, "DELETE");
                if (!empty($params)) {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
            default:
                break;
        }

        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt($ci, CURLOPT_URL, $url);
        if ($headers) curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ci);

        $event = new \app\components\Events\LogHttpRequestEvent([
            "url" => $url,
            "params" => $params,
            "method" => $method,
            "response" => $response,
            "obj" => $ci
        ]);
        $this->trigger(self::LOGGER, $event);

        curl_close($ci);
        return $response;
    }
}