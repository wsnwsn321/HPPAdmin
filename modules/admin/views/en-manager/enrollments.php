<?php
use yii\helpers\Html;
\app\assets\EnManagerAsset::register($this);
?>
<div style="padding: 0px; margin: 0px; clear: both">
    <a class="btn btn-app" style="margin-left: 0px;" href="<?php echo(urldecode(\yii::$app->getRequest()->get("returnURL"))); ?>">
        <i class="fa fa-edit"></i> 返回项目管理
    </a>
</div>
<div class="callout callout-danger" style="margin-bottom:10px;">
    <h4>注意!</h4>
    <p>请仅在必要时操作下列人员列表.</p>
</div>
<?php echo $this->render("../common/searchFirmUser.php", [
        "name" => $name,
        "contestName" => $c->name,
        "contestId" => $c->id,
        "enrollments" => $c->enrollments
]); ?>
<?php
$query = \app\models\Enrollments::find()->where(["contest_id" => $c->id]);
$dataProvider = new \yii\data\ActiveDataProvider([
    "query" => $query,
    "pagination" => ["pageSize" => 20,],
]);
echo \kartik\grid\GridView::widget([
    "dataProvider" => $dataProvider,
    "export" =>false,
    "hover" => true,
    "showFooter" => true,
    "columns" => [
        //"id",
        [
            "class" => "yii\grid\SerialColumn",
            "headerOptions" => ["width" => "5%"]
        ],
        [
            "attribute" => "realname",
            "headerOptions" => []
        ],
        [
            "class" => 'yii\grid\ActionColumn',
            'header' => '操作',
            'template' => '{recharge}',
            'buttons' => [
                "recharge" => function ($url, $model, $key) {
                    return Html::button("取消报名",[
                        "onClick" => "cancel('$model->id')",
                        "class" => "btn btn-block btn-info btn-xs",
                    ]);
                },
            ],
            "headerOptions" => ["width" => "5%"]
        ]
    ]
]);
?>