<?php

use dmstr\widgets\Menu;

?>
<?= dmstr\widgets\Menu::widget(
    [
        'items' => [
            ['label' => '参赛人员名单', 'icon' => 'file-code-o', 'url' => ['/admin/user/index']],
        ]
    ]

) ?>