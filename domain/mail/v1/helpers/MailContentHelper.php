<?php
namespace domain\mail\v1\helpers;

use domain\mail\v1\entities\AttachmentEntity;
use yii\web\HttpException;
use yii\web\UnprocessableEntityHttpException;
use yii2rails\domain\data\Query;
use yii2rails\extension\yii\helpers\FileHelper;

class MailContentHelper {

    const DEFAULT_WIDTH = 450;

    private static function loadDOMDocument($html) {
        if (empty($html) | $html == '') {
            return null;
        }
        $domDocument = new \DOMDocument();
        $domDocument->strictErrorChecking = false;
        //TODO: продумать иные варианты отлавливания WARNING
        set_error_handler(function ($severity, $message, $file, $line) {
            throw new HttpException($message, $code = 422, null);
        });
        $domDocument->loadHTML($html);
        restore_error_handler();
        return $domDocument;
    }

    public static function createBase64FromInnerImages($mailEntity) {
        $attachmentCollection = \App::$domain->mail->attachment->allByMailId($mailEntity->id);
        $mailEntity->attachments = $attachmentCollection;

        /** @var AttachmentEntity $attachment */
        $domDocument = self::loadDOMDocument($mailEntity->content);
        $imageCollection = self::getInnerImages($domDocument);

        $base64AttachmentCollection = [];
        $removeCollection = [];
        if (!empty($imageCollection)) {
            foreach ($imageCollection as $image) {
                foreach ($attachmentCollection as $attachment) {
                    if ($attachment->url == $image) {
                        $removeCollection[] = $attachment;

                        $query = new Query();
                        $query->andWhere(['code' => $attachment->extension]);
                        $fileExtensionEntity = \App::$domain->storage->fileExtension->one($query);

                        $fileContent = file_get_contents($attachment->url);
                        $fileContent = base64_encode($fileContent);
                        $base64InnerImg = 'data:' . $fileExtensionEntity->mime . ';base64,' . $fileContent;
                        $base64InnerImg = str_replace('"', '', $base64InnerImg);
                        $base64AttachmentCollection[] = $base64InnerImg;
                    }
                }
            }
            $mailEntity->attachments = self::removeImagesFromAttachments($removeCollection, $attachmentCollection);
            $mailEntity->content = self::removeImageCollection($imageCollection, $base64AttachmentCollection, $mailEntity->content);
        }

        return $mailEntity;
    }

    private static function removeImagesFromAttachments($attachmentCollection, $mailAttachmentCollection) {
        foreach ($attachmentCollection as $attachment) {
            $i = 0;
            foreach ($mailAttachmentCollection as $mailAttachment) {
                if ($attachment->id == $mailAttachment->id) {
                    array_splice($mailAttachmentCollection, $i, 1 );
                }
                $i++;
            }
        }
        return $mailAttachmentCollection;
    }

    private static function removeImageCollection($imageCollection, $uploadedFiles, $mailContent) {
        for ($i = 0; $i < count($imageCollection); $i++) {
            if (key_exists($i, $uploadedFiles)) {
                $attachment = $uploadedFiles[$i];
                $mailContent = str_replace($imageCollection[$i], $attachment, $mailContent);
            }
        }
        return $mailContent;
    }

    public static function loadInnerImagesAsAttachments($mailId, $mailContent) {
        if (empty($mailContent) | $mailContent == '') {
            return $mailContent;
        }
        try {
            $imageCollection = self::getBase64ImageCollection($mailContent);
        } catch (HttpException $e) {
            throw new UnprocessableEntityHttpException(\Yii::t('mail/mail', 'not_valid_html'), $code = 422, null);
        }
        $uploadedFiles = \App::$domain->mail->attachment->createFromBase64($mailId, $imageCollection);
        $mailContent = self::removeBase64ImageCollection($imageCollection, $uploadedFiles, $mailContent);
        $mailContent = self::setDefaultWidth($mailContent);
        return $mailContent;
    }

    private static function removeBase64ImageCollection($imageCollection, $uploadedFiles, $mailContent) {
        for ($i = 0; $i < count($imageCollection); $i++) {
            if (!empty($uploadedFiles[$i])) {
                $attachment = $uploadedFiles[$i]->url;
                $mailContent = str_replace($imageCollection[$i]['tag'], $attachment, $mailContent);
            }
        }
        return $mailContent;
    }

    private static function getBase64ImageCollection($mailContent) {
        $domDocument = self::loadDOMDocument($mailContent);
        $imageTagCollection = $domDocument->getElementsByTagName('img');
        $matches = [];
        $imageCollection = [];
        $mimeTypeMatchGroup = 2;
        for ($i = 0; $i < count($imageTagCollection); $i++) {
            $base64ImagePattern = '#(data:(.+?);base64,)(.+?)#';
            $imageTagSrc = $imageTagCollection[$i]->getAttribute('src');
            preg_match($base64ImagePattern, $imageTagSrc, $matches);
            if (isset($matches[$mimeTypeMatchGroup])) {
                $mimeType = $matches[$mimeTypeMatchGroup];
                $query = new Query();
                $query->andWhere(['mime' => $mimeType]);
                $type = \App::$domain->storage->fileExtension->one($query);
                $imageCollection[$i]['content'] = str_replace($matches[1], "", $imageTagSrc);
                $name = hash('crc32b', $imageCollection[$i]['content']);
                $imageCollection[$i]['name'] = $name . DOT . $type->code;
                $imageCollection[$i]['tag'] = $imageTagCollection[$i]->getAttribute('src');
            }
        }
        return $imageCollection;
    }

    public static function getAttachments($attachmentCollection) {
        $emailAttachments = [];
        if ($attachmentCollection) {
            foreach ($attachmentCollection as $attachmentEntity) {
                $fileEntity = \App::$domain->storage->person->oneByPath($attachmentEntity->path);
                $fileName = \Yii::getAlias('@frontend/web' . SL . $fileEntity->file_path);
                $fileName = FileHelper::normalizePath($fileName);
                $notifyAttachmentEntity = new \yii2lab\notify\domain\entities\AttachmentEntity;
                $notifyAttachmentEntity->fileName = $fileEntity->getFileName();
                $notifyAttachmentEntity->content = FileHelper::load($fileName);
                $emailAttachments[] = $notifyAttachmentEntity;
            }
        }
        return $emailAttachments;
    }

    public static function getAttachmentsByMailId($mailId) {
        $query = Query::forge();
        $query->andWhere(['mail_id' => $mailId]);
        /** @var AttachmentEntity[] $attachmentCollection */
        $attachmentCollection = \App::$domain->mail->attachment->all($query);
        $emailAttachments = [];
        if ($attachmentCollection) {
            foreach ($attachmentCollection as $attachmentEntity) {
                $fileEntity = \App::$domain->storage->person->oneByPath($attachmentEntity->path);
                $fileName = \Yii::getAlias('@frontend/web' . SL . $fileEntity->file_path);
                $fileName = FileHelper::normalizePath($fileName);
                $notifyAttachmentEntity = new \yii2lab\notify\domain\entities\AttachmentEntity;
                $notifyAttachmentEntity->fileName = $fileEntity->getFileName();
                $notifyAttachmentEntity->content = FileHelper::load($fileName);
                $emailAttachments[] = $notifyAttachmentEntity;
            }
        }
        return $emailAttachments;
    }

    public static function createBase64FromInnerImagesForMailRu($mailEntity) {
        $attachmentCollection = \App::$domain->mail->attachment->allByMailId($mailEntity->id);
        $mailEntity->attachments = $attachmentCollection;

        /** @var AttachmentEntity $attachment */
        $domContent = self::loadDOMDocument($mailEntity->content);
        $imageCollection = self::getInnerImages($domContent);

        $base64AttachmentCollection = [];
        $removeCollection = [];
        if (!empty($imageCollection)) {
            foreach ($imageCollection as $image) {
                foreach ($attachmentCollection as $attachment) {
                    if ($attachment->url == $image && $attachment->size < 50120) {
                        $removeCollection[] = $attachment;

                        $query = new Query();
                        $query->andWhere(['code' => $attachment->extension]);
                        $fileExtensionEntity = \App::$domain->storage->fileExtension->one($query);

                        $fileContent = file_get_contents($attachment->url);
                        $fileContent = base64_encode($fileContent);
                        $base64InnerImg = 'data:' . $fileExtensionEntity->mime . ';base64,' . $fileContent;
                        $base64InnerImg = str_replace('"', '', $base64InnerImg);
                        $base64AttachmentCollection[] = $base64InnerImg;
                    }
                }
            }

            $mailEntity->attachments = self::removeImagesFromAttachments($removeCollection, $attachmentCollection);
            $mailEntity->content = self::removeImageCollection($imageCollection, $base64AttachmentCollection, $mailEntity->content);
        }
        return $mailEntity;
    }

    private static function getInnerImages($domDocument) {
        if (empty($domDocument)) {
            return null;
        }
        $imageTagCollection = $domDocument->getElementsByTagName('img');
        $imageCollection = [];
        if (count($imageTagCollection) > 0) {
            for ($i = 0; $i < count($imageTagCollection); $i++) {
                $imageCollection[] = $imageTagCollection[$i]->getAttribute('src');
            }
        }
        return $imageCollection;
    }

    private static function setDefaultWidth($content) {
        $pattern = "#<img(.+?)\/{0,1}>#";
        $matches = [];
        preg_match_all($pattern, $content, $matches);
        foreach ($matches[0] as $imageTag) {
            $image = str_replace('<img', '<img style="max-width:' . self::DEFAULT_WIDTH . 'px;"' , $imageTag);
            $content = str_replace($imageTag, $image, $content);
        }
        return $content;
    }

}