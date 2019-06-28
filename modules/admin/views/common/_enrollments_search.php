<tr>
    <td><?php echo($model->id); ?></td>
    <td><?php echo($model->nickname); ?></td>
    <td><?php echo($model->fullname); ?></td>
    <td>
        <?php echo  \yii\helpers\Html::button("替TA报名",[
                "onClick" => "enroll(".$model->userId.", ".$contestId.")",
            "class" => "btn btn-block btn-success btn-xs",
            ]);?>
    </td>
</tr>