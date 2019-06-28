<?php
return array_merge(
    [
        "adminEmail" => "admin@example.com",
        "retMsg" => [
            "global" => [
                "success" => ["code" => 10001, "message" => "succeed!"],
                "vf" => ["code" => -10001, "message" => "Validation error!"], // Validated failed => vf
                "sf" => ["code" => -10000, "message" => "Saved failed"], // Saved to db failed,
                "id_not_found" => ["code" => -10002, "message" => "data not found"]
            ],
            "global_exception" => [
                "generate_number_failed" => ["code" => -21001, "message" => "Generate number automatically failed!"]
            ]
        ],
        "ApiHost" => "http://php7wechatapi.happypingpang.com/",
        "WapHost" => "http://wp.happypingpang.com/",
        "afterLoginRedirect" => "http://wp.happypingpang.com/users/identity",
        "admin_firm" => [
            1=>3,
            55=> 3
        ]
    ],
    require_once dirname(__file__)."/restconfig.php"
);