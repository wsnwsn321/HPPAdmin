<form name="form1" method="post">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title"><?php echo($c->name); ?></h3>
                    <div class="box-tools">
                        <div class="input-group input-group-sm" style="width: 240px;">
                            <input type="text" name="table_search" value="<?php echo($name); ?>" class="form-control pull-right" placeholder="请输入用户真实姓名">
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.box-header -->
            </div>
        </div>
    </div>
</form>
<?php
use kartik\grid\GridView;
use kartik\editable\Editable;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\firmusers;
use app\assets\DeleteAllAsset;
//search box
$name = "";
if (\yii::$app->getRequest()->isPost) {
    $name = trim(\yii::$app->getRequest()->post("table_search"));
}
if($name==''){
    $query = firmusers::find()->where(['firmId'=>\yii::$app->params['admin_firm'][\yii::$app->getUser()->getId()]])->with('wx','credits','profileData');
}
else{
    $query = firmusers::find()->where(['firmId'=>\yii::$app->params['admin_firm'][\yii::$app->getUser()->getId()]])->andWhere(["like", "fullname", $name."%", false])->with('wx','credits','profileData');
}
//$query = firmusers::find();
$dataProvider = new ActiveDataProvider(
        [
            'query' => $query,
            'pagination' => ['pageSize' => 20,],
        ]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'export' =>false,
    'hover' => true,
    'showFooter' => true,
    'columns' => [
//
//        [
//            'class' => 'yii\grid\CheckboxColumn',
//            'footer' => '<button id="multipleDelete">批量删除</button>',
//            'footerOptions' => ['colspan' => 0],
//        ],
        'userId',
        [
            'label' => '用户名',
            'value' =>  'wx.username',
        ],
        [
            'label' => '微信名',
            'value' =>  'nickname',
        ],

        [
            'attribute' => 'fullname',
            'class'=>'kartik\grid\EditableColumn',
            'editableOptions'=>[
                'asPopover' => false,
                'inputType'=>\kartik\editable\Editable::INPUT_TEXTAREA,
                'options' => [
                    'rows' => 2,
                ],
            ],
        ],
        [
            'attribute' => 'mobile',
            'class'=>'kartik\grid\EditableColumn',
            'editableOptions'=>[
                'asPopover' => false,
                'inputType'=>\kartik\editable\Editable::INPUT_TEXTAREA,
                'options' => [
                    'rows' => 2,
                ],
            ],
        ],
        [
            'label' => '积分',
            'value' =>  'credits.amount',
            'attribute' => "credit",
            'class'=>'kartik\grid\EditableColumn',
            'editableOptions'=>[
                'asPopover' => false,
                'inputType'=>\kartik\editable\Editable::INPUT_TEXTAREA,
                'options' => [
                    'rows' => 2,
                ],
            ],
        ],
        [
            'label' => '性别',
            'value' => function ($model) {
                $state = [
                    '0' => '女',
                    '1' => '男',
                ];
                return $state[$model->profileData->gender];
            },
        ],

        ]
]);
