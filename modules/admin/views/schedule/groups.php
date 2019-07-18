<?php
/** CaryYe 2019/7/8 1:10 PM */
\app\assets\GroupAsset::register($this);
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
        <h3 class="box-title">分组预览</h3>
    </div>
    <div class="box-header with-border">
        <button class="btn btn-default" onclick="window.location.href='<?php echo($url1); ?>'">分组设置</button>
        &nbsp;
        <button class="btn btn-default" disabled="disable">分组预览</button>
        &nbsp;
        <button class="btn btn-default" <?php if($c->status == "closed") { ?>onclick="window.location.href='<?php echo($url3); ?>'"<?php } else { echo("disabled='disabled'"); } ?>>编排设置</button>
    </div>
    <!-- /.box-header -->
    <!-- form start -->
    <form class="form-horizontal">
        <div class="box-body">
            <div class="form-group">
                <div class="col-sm-10" style="width: 100%;">
                    <?php $j = 0; foreach($groupedOpponents as $k => $v) { ?>
                    <!-- Group start -->
                    <div class="box" style="width: 49%; float: <?php if($j %2 == 0) echo("left"); else echo("right"); ?>;">
                        <div class="box-header with-border">
                            <h3 class="box-title">第 <?php echo($k); ?> 组</h3>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <tbody><tr>
                                    <th style="width: 45px">序列</th>
                                    <th style="width: 45px">头像</th>
                                    <th>真实姓名</th>
                                    <th style="width: 40px">操作</th>
                                </tr>
                                <?php
                                    $dataProvider = new \yii\data\ArrayDataProvider([
                                        "allModels" => $v
                                    ]);
                                    echo \yii\widgets\ListView::widget([
                                        "dataProvider" => $dataProvider,
                                        "itemView" => "_groups",
                                        "showOnEmpty" => true,
                                        "layout" => "{items}",
                                        "viewParams" => [//Arguments passed to every item.
                                            "contestId" => $c->id
                                        ],
                                        "itemOptions" => [
                                        ],
                                    ]);
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Group end -->
                    <?php $j++; } ?>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
            <!-- <button type="submit" class="btn btn-default">Cancel</button> -->
            <button type="button" onclick="window.location.href='<?php echo($url3); ?>'" class="btn btn-info pull-right">确认</button>
        </div>
        <!-- /.box-footer -->
    </form>
</div>
