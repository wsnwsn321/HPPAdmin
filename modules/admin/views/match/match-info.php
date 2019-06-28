<?php
use kartik\grid\GridView;
use kartik\editable\Editable;
use yii\data\ActiveDataProvider;
use  \app\modules\admin\models\games;
use yii\helpers\Html;
$query = games::find()->where(['firmId'=>\yii::$app->params['admin_firm'][\yii::$app->getUser()->getId()]])->with('contests');
$dataProvider = new ActiveDataProvider(
    [
        'query' => $query,
        'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        'pagination' => ['pageSize' => 20,],
    ]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'export' =>false,
    'hover' => true,
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
            'attribute' => 'scheduled_end_date',
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
            'label'=>'比赛地点',
            'value'=> 'contests.arena',
            'attribute' => "arena",
            'class'=>'kartik\grid\EditableColumn',
            'editableOptions'=>[
                'asPopover' => false,
                'inputType'=>\kartik\editable\Editable::INPUT_TEXTAREA,
                'options' => [
                    'rows' => 2,
                ],
            ],

            ],


        [  'class' => 'yii\grid\ActionColumn',
            'header' => '管理',
            'template' => '{recharge}',
            'buttons' => [
                "recharge" => function ($url, $model, $key) {
                    return Html::button("进入赛事",[
                        //"onClick" => "window.location.href='/admin/en-manager/enrollments?id=".$model->contests->id."'",
                        "onClick" => "window.location.href='/admin/match/contests?id=".$model->contests->id."&page=".urlencode($_SERVER['REQUEST_URI'])."'",
                        "class" => "btn btn-default",
                    ]);
                }
            ],
        ],
    ],

    ]);