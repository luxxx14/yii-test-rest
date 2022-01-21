<?php
namespace domain\mail\v1\strategies\mail;

use domain\mail\v1\entities\MailEntity;
use domain\mail\v1\strategies\mail\receive\ReceiveMailStrategy;
use domain\mail\v1\strategies\mail\receive\HTMLEntities;
use domain\mail\v1\strategies\mail\receive\Rambler;
use domain\mail\v1\strategies\mail\receive\ttc;

class ReceiveStrategy implements ReceiveMailStrategy {

    private static $instance;
    private $strategy;
    private $strategyCollection;

    private function __construct() {
        $this->initStrategy();
    }

    private function initStrategy() {
        $this->strategyCollection = [
            'default' => HTMLEntities::getInstance(),
            'rambler' => Rambler::getInstance(),
            'ttc' => ttc::getInstance(),
        ];
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function receive($mailEntity) : MailEntity {
        return $this->strategy->receive($mailEntity);
    }

    public function setStrategy($name) {
        if (!array_key_exists($name, $this->strategyCollection)) {
            $name = 'default';
        }
        $this->strategy = $this->strategyCollection[$name];
    }
}