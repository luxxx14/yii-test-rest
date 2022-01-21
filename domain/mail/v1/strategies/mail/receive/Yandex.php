<?php
namespace domain\mail\v1\strategies\mail\receive;

use domain\mail\v1\entities\MailEntity;
use domain\mail\v1\strategies\mail\receive\ReceiveMailStrategy;

class Yandex implements ReceiveMailStrategy {

    private static $instance;

    private function __construct() {

    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function receive($mailEntity) : MailEntity
    {
        return $mailEntity;
    }
}