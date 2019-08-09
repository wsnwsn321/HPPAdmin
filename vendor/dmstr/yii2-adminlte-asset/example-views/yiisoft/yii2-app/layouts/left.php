<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<? echo(\yii::$app->user->getIdentity()->getAvatar()); ?>" width="160" height="160" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p><?php echo(\yii::$app->user->getIdentity()->getName()); ?></p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <!-- search form -->
        <!-- <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form> -->
        <!-- /.search form -->

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    ['label' => '快乐乒乓微信 SaaS', 'options' => ['class' => 'header']],
                    ['label' => '用户管理', 'icon' => 'users', 'url' => ['/admin/user/index']],
                    ['label' => '赛事信息调整', 'icon' => 'calendar', 'url' => '#','items'=>[
                            ['label' => '赛事信息修改', 'icon' => 'edit', 'url' => ['/admin/match/match-info'],],
                        ]
                    ],
                    ['label' => '报名费用管理', 'icon' => ' fa-rmb', 'url' =>'#','items'=>[
                            ['label' => '赛事流水', 'icon' => 'shopping-cart', 'url' => ['/admin/payment/check-bill'],],


                        ]
                    ],
                    ['label' => '俱乐部管理', 'icon' => 'handshake-o', 'url' =>'#','items'=>[
                        ['label' => '俱乐部新闻管理', 'icon' => 'newspaper-o', 'url' => ['/admin/club/index'],],
                        ['label' => '俱乐部简介管理', 'icon' => 'id-card', 'url' => ['/admin/club/intro'],],
                        ['label' => '俱乐部教练管理', 'icon' => 'user-plus', 'url' => ['/admin/club/coach'],],
                    ]
                    ]
                    /*['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest],
                    [
                        'label' => ' tools',
                        'icon' => 'share',
                        'url' => '#',
                        'items' => [
                            ['label' => 'Gii', 'icon' => 'file-code-o', 'url' => ['/gii'],],
                            ['label' => 'Debug', 'icon' => 'dashboard', 'url' => ['/debug'],],
                            [
                                'label' => 'Level One',
                                'icon' => 'circle-o',
                                'url' => '#',
                                'items' => [
                                    ['label' => 'Level Two', 'icon' => 'circle-o', 'url' => '#',],
                                    [
                                        'label' => 'Level Two',
                                        'icon' => 'circle-o',
                                        'url' => '#',
                                        'items' => [
                                            ['label' => 'Level Three', 'icon' => 'circle-o', 'url' => '#',],
                                            ['label' => 'Level Three', 'icon' => 'circle-o', 'url' => '#',],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],*/
                ],
            ]
        ) ?>

    </section>

</aside>
