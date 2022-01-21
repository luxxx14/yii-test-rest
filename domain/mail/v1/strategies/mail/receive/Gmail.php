<?php
namespace domain\mail\v1\strategies\mail\receive;

use domain\mail\v1\entities\AttachmentEntity;
use domain\mail\v1\entities\MailEntity;
use yii2rails\domain\data\Query;

class Gmail implements ReceiveMailStrategy {

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
        $content = html_entity_decode($mailEntity->content, ENT_HTML5, 'UTF-8');
        $mailEntity->content = html_entity_decode($content, ENT_HTML5, 'UTF-8');
        $mailEntity->content = $this->convertImages($mailEntity);
        return $mailEntity;
    }

    public function convertImages($mailEntity) {
        $imageRegex = "#\[image:(.+?)\]#";
        preg_match_all($imageRegex, $mailEntity->content, $matches);
        $query = Query::forge();
        $query->andWhere(['mail_id' => $mailEntity->id]);
        /** @var AttachmentEntity[] $attachmentCollection */
        $attachmentCollection = \App::$domain->mail->attachment->all($query);
        $content = $mailEntity->content;
        if (!empty($attachmentCollection) && !empty($matches[1])) {
            foreach ($attachmentCollection as $attachment) {
                for($i = 0; $i < count($matches[1]); $i++) {
                    if (trim($attachment->file_name) == trim($matches[1][$i])) {
                        $img = '<img src="'.$attachment->getUrl().'" >';
                        $content = str_replace($matches[0][$i], $img, $mailEntity->content);
                    }
                }
            }
            return $content;
        } else {
            return $mailEntity->content;
        }
    }
}