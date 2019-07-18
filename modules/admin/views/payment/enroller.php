<?php

use app\modules\admin\models\wechatpay_orderinfo;
use kartik\grid\GridView;
use kartik\editable\Editable;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\firmusers;
use app\assets\DeleteAllAsset;
$v = $_GET['id'];
$page = $_GET['page'];
$query = wechatpay_orderinfo::find()->where(['contest_id'=>$v])->andWhere(['confirm_pay'=>1])->with('userName');
$dataProvider = new ActiveDataProvider(
    [
        'query' => $query,
        'pagination' => ['pageSize' => 20,],
    ]);
?>
    <div style="padding: 0px; margin: 0px; clear: both">
        <a class="btn btn-app" style="margin-left: 0px;" href="<?php echo(urldecode(\yii::$app->getRequest()->get("returnURL"))); ?>">
            <i class="fa fa-edit"></i> 返回项目管理
        </a>
    </div>
<?
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'export' =>false,
    'hover' => true,
    'showFooter' => true,
    'columns' => [
        'user_id',
        'userName.fullname',
        'out_trade_no',
        'total_fee',
        'return_timestamp',
    ],
]);
?>