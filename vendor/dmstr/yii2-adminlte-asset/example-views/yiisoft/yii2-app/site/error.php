<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
?>
<section class="content">

    <div class="error-page">
        <h2 class="headline text-info"><i class="fa fa-warning text-yellow"></i></h2>

        <div class="error-content">
            <h3><?= $name ?></h3>

            <p>
                <?= nl2br(Html::encode($message)) ?>
            </p>

            <p>
                The above error occurred while the Web server was processing your request.<br />
                服务器在处理请求的时候发生上述错误.<br />&nbsp;<br />
                Please contact us if you think this is a server error. Thank you.<br />
                如果你觉得这是一个服务器错误, 请联系我们. 谢谢 .<br />&nbsp;<br />
                Meanwhile, you may <a href='/admin/user/index'>return to user management</a> or you may <a href="javascript::void(0)" onclick="window.history.go(-1);">return to the previous page.</a><br />
                同时 , 你可以 <a href='/admin/user/index'>返回到用户管理</a> 或者你可以 <a href="javascript::void(0)" onclick="window.history.go(-1);">返回之前操作的页面</a>.
            </p>

            <!-- <form class='search-form'>
                <div class='input-group'>
                    <input type="text" name="search" class='form-control' placeholder="Search"/>

                    <div class="input-group-btn">
                        <button type="submit" name="submit" class="btn btn-primary"><i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </form> -->
        </div>
    </div>

</section>
