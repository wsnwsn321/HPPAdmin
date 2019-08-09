<?php

use \app\modules\admin\models\coaches;
use kartik\editable\Editable;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
$query = coaches::find();
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
            'attribute' => 'credit',
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
            'attribute' => 'age',
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
            'attribute' => 'teachingAge',
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
            'attribute' => 'sex',
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
            'attribute' => 'fee',
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
            'attribute' => 'honor',
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
            'attribute' => 'grade',
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