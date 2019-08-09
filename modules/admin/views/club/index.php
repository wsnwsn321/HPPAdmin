<?php

use \app\modules\admin\models\association_news;
use kartik\editable\Editable;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
$query = association_news::find();
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
            'attribute' => 'title',
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
            'attribute' => 'author',
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
            'attribute' => 'date',
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
            'attribute' => 'cover',
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
            'attribute' => 'summary',
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
            'label'=>'新闻内容',
            'attribute' => 'content',
            'value' => 'content',
            'class'=>'kartik\grid\EditableColumn',
            'editableOptions'=>[
                'asPopover' => false,
                'inputType'=>Editable::INPUT_TEXTAREA,
                'options' => [
                    'rows' => 2,
                ],
            ],
            'headerOptions' => ['width' => '200']
        ],
    ]

]);