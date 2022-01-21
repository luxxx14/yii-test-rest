<?php
namespace domain\mail\v1\strategies\mail\send;

use domain\mail\v1\helpers\MailContentHelper;
use domain\mail\v1\strategies\mail\send\SendOuterMailStrategy;
use yii2lab\notify\domain\entities\EmailEntity;

class WithAttachmentsAndBase64 implements SendOuterMailStrategy {

    private static $instance;

    private function __construct() {

    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function send($mailEntity) {
        $mailEntity = MailContentHelper::createBase64FromInnerImages($mailEntity);
        $emailEntity = new EmailEntity;
        $emailEntity->from = $mailEntity->from;
        $emailEntity->address = $mailEntity->to;
        if (isset($mailEntity->copy_to) && !empty($mailEntity->copy_to)) {
            $emailEntity->copyToAdress = $mailEntity->copy_to;
        }
        if (isset($mailEntity->blind_copy) && !empty($mailEntity->blind_copy)) {
            $emailEntity->blindCopyToAddress = $mailEntity->blind_copy;
        }
        if (isset($mailEntity->reply_to) && !empty($mailEntity->reply_to)) {
            $emailEntity->replyToAddress = $mailEntity->reply_to;
        }
        $emailEntity->subject = $mailEntity->subject;
        $emailEntity->content = $mailEntity->content;
        $emailEntity->attachments = MailContentHelper::getAttachments($mailEntity->attachments);
        \App::$domain->notify->email->directSendEntity($emailEntity);
    }
}