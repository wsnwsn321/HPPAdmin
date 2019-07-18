<?php
if (!isset(\yii::$app->params["admin_firm"][\yii::$app->getUser()->getId()])){
    throw new \yii\web\HttpException(500, "You are not accessed to the backend.");
}

if ($name != '') {
    $game = $c->game;
    $weChatId = $game->wechatId;
    $firmId = \yii::$app->params["admin_firm"][\yii::$app->getUser()->getId()];
    $firmCond = (int)$firmId === 0 ? [] : ["firmId" => $firmId];

    $ids = [];
    if (is_array($enrollments) && !empty($enrollments)) {
        foreach ($enrollments as $k => $v) {
            array_push($ids, $v->user_id);
        }
    }

    $query = \app\models\FirmUsers::find()
        ->where($firmCond)
        ->andWhere(["like", "fullname", $name."%", false])
        /*->andWhere([
            "or",
            ["like", "nickname", $name . '%', false],
            ["like", "fullname", $name . '%', false]
        ])*/->andWhere([
            "not in", "userId", $ids
        ]);
    $dataProvider = new \yii\data\ActiveDataProvider([
        "query" => $query,
        "pagination" => ["pageSize" => 5],
    ]);
}
?>
<form name="form1" method="post">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">搜索并为 <font color="#3c8dbc"><?php echo($contestName); ?></font> 添加报名选手 :</h3>
                    <div class="box-tools">
                        <div class="input-group input-group-sm" style="width: 166px;">
                            <input type="text" name="table_search" value="<?php echo($name); ?>" class="form-control pull-right" placeholder="请输入用户真实姓名">
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tr>
                            <th width="10%">ID</th>
                            <!-- <th width="40%">Nickname</th> -->
                            <th width="80%">真实姓名</th>
                            <th>操作</th>
                        </tr>
                        <?php if ($name != '') echo \yii\widgets\ListView::widget([
                            'dataProvider' => $dataProvider,
                            'itemView' => '_enrollments_search',
                            "showOnEmpty" => true,
                            'layout' => '{items}',
                            'viewParams' => [//传参数给每一个item
                                'contestId' => $contestId
                            ],
                            'itemOptions' => [
                            ],
                        ]);?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>
