<?php
namespace domain\mail\v1\strategies\mail;

use domain\mail\v1\helpers\MailHelper;
use domain\mail\v1\strategies\mail\send\WithAttachments;
use domain\mail\v1\strategies\mail\send\Mail;
use domain\mail\v1\strategies\mail\send\WithAttachmentsAndBase64;
use domain\mail\v1\strategies\mail\send\SendOuterMailStrategy;
use yii2rails\extension\yii\helpers\ArrayHelper;

class SendStrategy implements SendOuterMailStrategy {

    private static $instance;
    private $strategy;
    private $strategyCollection;
    private $servicePriorityMap = [
        'gmail' => 0,
        'ttc' => 0,
        'rambler' => 0,
        'mail' => 1,
        'yandex' => 2,
        'default' => 2,
    ];

    private function __construct() {
        $this->initStrategyCollection();
    }

    private function initStrategyCollection() {
        $this->strategyCollection = [
            'yandex' => WithAttachmentsAndBase64::getInstance(),
            'ttc' => WithAttachments::getInstance(),
            'mail' => Mail::getInstance(),
            'gmail' => WithAttachments::getInstance(),
            'rambler' => WithAttachments::getInstance(),
            'default' => WithAttachmentsAndBase64::getInstance(),
        ];
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function send($mailEntity) {
        $this->setStrategy($mailEntity);
        $this->strategy->send($mailEntity);
    }

    public function setStrategy($mailEntity) {
        $serviceNameCollection = $this->getServiceNameCollection($mailEntity);
        $activeServiceName = 'default';
        foreach ($serviceNameCollection as $serviceName) {
            $activeServiceName = self::checkServiceByPriority($activeServiceName, $serviceName);
        }
        $this->strategy = $this->strategyCollection[$activeServiceName];
    }

    private function checkServiceByPriority (string $activeServiceName, string $serviceName) {
        if (!key_exists($serviceName, $this->servicePriorityMap)) {
            return $activeServiceName;
        }
        if ($this->servicePriorityMap[$activeServiceName] > $this->servicePriorityMap[$serviceName]) {
            $activeServiceName = $serviceName;
        }
        return $activeServiceName;
    }

    private function getServiceNameCollection($mailEntity) {
        $copyTo = [];
        if (isset($mailEntity->copy_to)) {
            $copyTo = $mailEntity->copy_to;
        }
        $blindCopy = [];
        if (isset($mailEntity->blind_copy)) {
            $blindCopy = $mailEntity->blind_copy;
        }
        $emailCollection = array_merge($mailEntity->to, $blindCopy, $copyTo);
        foreach ($emailCollection as $email) {
            $serviceNameCollection[] = MailHelper::getServiceNameByEmail($email);
        }
        $serviceNameCollection = array_unique($serviceNameCollection);
        return $serviceNameCollection;
    }
}