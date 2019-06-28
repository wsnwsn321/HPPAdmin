<?php
use kartik\grid\GridView;
use kartik\editable\Editable;
use yii\data\ActiveDataProvider;
use  \app\modules\admin\models\contests;
use yii\helpers\Html;
$v = $_GET['id'];
$page = $_GET['page'];
$query = contests::find()->where(['id'=>$v]);
$dataProvider = new ActiveDataProvider(
    [
        'query' => $query,
        'pagination' => ['pageSize' => 20,],
    ]);
?>
<div style="padding: 0px; margin: 0px; clear: both">
    <a class="btn btn-app" style="margin-left: 0px;" href="<?php echo urldecode($page);?>">
        <i class="fa fa-edit"></i> 返回赛事信息
    </a>
</div>

<?
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'export' =>false,
    'hover' => true,
    'showFooter' => true,
    'columns' => [
        'id',
        [
            'attribute' => 'name',
            'class'=>'kartik\grid\EditableColumn',
            'editableOptions'=>[
                'asPopover' => false,
                'inputType'=>Editable::INPUT_TEXTAREA,
                'options' => [
                    'rows' => 2,
                ],
            ],
        ],
        [
            'attribute' => 'scheduled_start_date',
//            'class'=>'kartik\grid\EditableColumn',
//            'editableOptions'=>[
//                'asPopover' => false,
//                'inputType'=>Editable::INPUT_TEXTAREA,
//                'options' => [
//                    'rows' => 2,
//                ],
//            ],
        ],

        [
            'label'=>'报名人数',
            'value'=> 'enroll_count',
        ],

        [  'class' => 'yii\grid\ActionColumn',
            'header' => '管理',
            'template' => '{people}{credit}{schedule}',
            'buttons' => [
                "people" => function ($url, $model, $page) {
                    return Html::button("人员管理",[
                        "onClick" => "window.location.href='/admin/en-manager/enrollments?id=".$model->id."&returnURL=".urlencode($_SERVER['REQUEST_URI'])."'",
                        "class" => "btn btn-default",
                    ]);
                },
                 "credit" => function ($url, $model, $key) {
                    return Html::button("修改积分限制",[
                        "onClick" => "window.location.href='/admin/en-manager/enrollments?id=".$model->id."'",
                        "class" => "btn btn-default",
                        ]);
                    },
                "schedule" => function ($url, $model, $key) {
                    return Html::button("修改对阵",[
                        "onClick" => "window.location.href='/admin/en-manager/enrollments?id=".$model->id."'",
                        "class" => "btn btn-default",
                    ]);
                },

            ],
        ],
    ],

]);
?>