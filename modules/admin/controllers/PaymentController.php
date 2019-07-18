<?php
/** CaryYe 2019/6/25 1:44 PM */
namespace app\modules\admin\controllers;

class PaymentController extends LoginController
{
    public function actionEnroller()
    {
        return $this->render('enroller',[
        ]);
    }

    public function actionCheckBill()
    {
        return $this->render('check-bill',[
        ]);
    }
}