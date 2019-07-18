<?php
$opponent = $model->opponent;
?>
<tr>
    <td><?php echo($model->serial); ?></td>
    <td><img src="<?php echo($opponent->avatar); ?>" width="22" height="22"></td>
    <td><?php echo($opponent->fullname); ?></td>
    <td><button type="button" class="btn btn-block btn-info btn-xs" osid="<?php echo($model->id); ?>" onclick="exchange(this);">交换</button></td>
</tr>