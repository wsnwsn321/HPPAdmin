<?php
/* CaryYe 2018/7/26 10:33 AM */
namespace app\controllers\ActiveWeApis;

/**
 * Class TemplateIndustryAction
 * @package app\controllers\ActiveWeApis
 */
class TemplateIndustryAction extends \yii\base\Action
{
    /**
     * Runs the Action.
     * @return string
     */
    public function run()
    {
        $m = new \app\models\wx\Template();

        return \yii::$app->request->isPost
            ? $m->setIndustries()
            : $m->getIndustries();
    }
}