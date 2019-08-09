<?php

use \app\modules\admin\models\association_intro;
use kartik\editable\Editable;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
$query = association_intro::find();
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
        'id',
        [
            'attribute' => 'introduction',
            'class'=>'kartik\grid\EditableColumn',
            'editableOptions'=>[
                'asPopover' => false,
                'inputType'=>Editable::INPUT_TEXTAREA,
                'options' => [
                    'rows' => 2,
                ],
            ],
        ],
    ]

]);