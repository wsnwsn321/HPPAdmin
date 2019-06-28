<?php
/* CaryYe 2018/4/12 9:06 AM */
namespace app\WxComponents;

/**
 * Class Tools
 * @package app\WxComponents
 */
class Tools extends \yii\base\Component
{
    /**
     * @param string $body
     * @return array
     */
    public static function xml2arr($body)
    {
        $ret = [];
        if (trim($body === "")) return $ret;
        $xml = simplexml_load_string($body);

        foreach ($xml->children() as $child) {
            $tName = $child->getName();
            $ret[$tName] = (string) $xml->$tName;
        }

        return $ret;
    }
}