<?php
/** CaryYe 2019/7/10 7:39 AM */
\app\assets\SettingAsset::register($this);
?>
<div style="padding: 0px; margin: 0px; clear: both">
    <a class="btn btn-app" style="margin-left: 0px;" href="<?php echo(urldecode(\yii::$app->getRequest()->get("returnURL"))); ?>">
        <i class="fa fa-edit"></i> 返回项目管理
    </a>
</div>
<div class="callout callout-danger" style="margin-bottom:10px;">
    <h4>注意!</h4>
    <p><b>重要 : 一旦确认分组 , 该比赛无法再被报名 , 请务必确认此点 !!!</b></p>
    <p>编排不能撤销 , 同一场比赛 , 也请勿分别在手机端及后台操作 , 一场比赛仅可使用一种工具 (后台或手机端) 进行操作.</p>
</div>
<div class="callout callout-info">
    <h4>正在编排比赛 :</h4>
    <p><?php echo($c->name); ?></p>
</div>
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">编排设置</h3>
    </div>
    <div class="box-header with-border">
        <button class="btn btn-default" onclick="window.location.href='<?php echo($url1); ?>'">分组设置</button>
        &nbsp;
        <button class="btn btn-default" <?php if($c->status == "closed") { ?>onclick="window.location.href='<?php echo($url2); ?>'"<?php } else { echo("disabled='disabled'"); } ?>>分组预览</button>
        &nbsp;
        <button class="btn btn-default" disabled='disabled'>编排设置</button>
    </div>
    <!-- /.box-header -->
    <!-- form start -->
        <div class="box-body">
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">请选择分组赛制 </label>
                <div class="col-sm-10">
                    <button type="button" style="margin-bottom: 10px;" value="3" class="btn btn-block btn-success btn-lg">三局两胜制</button>
                    <button type="button" style="margin-bottom: 10px;" value="5" class="btn btn-block btn-info btn-lg">五局三胜制</button>
                    <button type="button" value="7" class="btn btn-block btn-info btn-lg">七局四胜制</button>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
            <!-- <button type="submit" class="btn btn-default">Cancel</button> -->
            <button type="button" id="submitSchedule" class="btn btn-info pull-right">确认</button>
        </div>
        <!-- /.box-footer -->
</div>
<input type="hidden" id="PathUrl2" value="<?php echo \yii::$app->getRequest()->get("returnURL", ""); ?>" />
<script type="text/javascript">
    window.onload = function() {
        var game = new ScheduleSubmit("<?php echo($c->id); ?>");
        game.init();
    }
</script>