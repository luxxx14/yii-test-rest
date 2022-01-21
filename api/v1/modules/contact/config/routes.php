<?php

$version = API_VERSION_STRING;

return [
    ["class" => "yii\\rest\UrlRule", "controller" => ["{$version}/contact-personal" => "contact/personal"]],
    ["class" => "yii\\rest\UrlRule", "controller" => ["{$version}/contact-recent" => "contact/recent"]],
];
