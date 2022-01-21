<?php

$version = API_VERSION_STRING;

return [
    "GET {$version}/new-message" => "mail/mail/new-message",
    "OPTIONS {$version}/new-message" => "mail/mail/new-message",
    "GET {$version}/settings" => "mail/settings/view",
    "GET {$version}/box-size-quote" => "mail/mail/box-size-quote",
    "PUT {$version}/settings" => "mail/settings/update",
    "OPTIONS {$version}/settings" => "mail/settings/options",

    "PUT {$version}/mail/move/<idCollection>" => "mail/mail/move",
    "OPTIONS {$version}/mail/move/<idCollection>" => "mail/mail/options",

    "PUT {$version}/mail/touch/<idCollection>" => "mail/mail/touch",
    "OPTIONS {$version}/mail/touch/<idCollection>" => "mail/mail/touch",

    "PUT {$version}/dialog/touch/<id>" => "mail/dialog/touch",
    "OPTIONS {$version}/dialog/touch/<id>" => "mail/dialog/touch",

    "PUT {$version}/discussion/touch/<id>" => "mail/discussion/touch",
    "OPTIONS {$version}/discussion/touch/<id>" => "mail/discussion/touch",

    "POST {$version}/draft/send/<id>" => "mail/draft/send",
    "OPTIONS {$version}/draft/send/<id>" => "mail/draft/options",

    "DELETE {$version}/dialog-message/<id>" => "mail/dialog/message-delete",
    "OPTIONS {$version}/dialog-message/<id>" => "mail/dialog/options",

    "DELETE {$version}/discussion-message/<id>" => "mail/discussion/message-delete",
    "OPTIONS {$version}/discussion-message/<id>" => "mail/discussion/options",

    //"POST {$version}/draft-attachment" => "mail/draft-attachment/upload",
    //"OPTIONS {$version}/draft-attachment" => "mail/draft-attachment/options",

    "POST {$version}/mail-receiver" => "mail/mail/receiver",
    "POST {$version}/mail-receiver-form" => "mail/mail/receiver-form",
    ["class" => "yii\\rest\UrlRule", "controller" => ["{$version}/mail" => "mail/mail"]],
    ["class" => "yii\\rest\UrlRule", "controller" => ["{$version}/mail-dialog" => "mail/dialog"]],
    ["class" => "yii\\rest\UrlRule", "controller" => ["{$version}/dialog" => "mail/dialog"]],
    ["class" => "yii\\rest\UrlRule", "controller" => ["{$version}/discussion" => "mail/discussion"]],
    ["class" => "yii\\rest\UrlRule", "controller" => ["{$version}/folder" => "mail/folder"]],
    ["class" => "yii\\rest\UrlRule", "controller" => ["{$version}/draft" => "mail/draft"]],
    ["class" => "yii\\rest\UrlRule", "controller" => ["{$version}/discussion-member" => "mail/discussion-member"]],
    ["class" => "yii\\rest\UrlRule", "controller" => ["{$version}/draft-attachment" => "mail/draft-attachment"]],
];
