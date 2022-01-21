<?php

namespace domain\mail\v1\services;

use domain\mail\v1\entities\AttachmentEntity;
use domain\mail\v1\entities\BoxEntity;
use domain\mail\v1\forms\UploadFileCollectionForm;
use domain\mail\v1\forms\UploadForm;
use domain\mail\v1\interfaces\services\AttachmentInterface;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii2rails\domain\BaseEntity;
use yii2rails\domain\data\Query;
use yii2rails\domain\exceptions\UnprocessableEntityHttpException;
use yii2rails\domain\helpers\ErrorCollection;
use yii2rails\domain\services\base\BaseActiveService;
use yii2rails\extension\common\enums\StatusEnum;
use yii2rails\extension\common\helpers\TempHelper;
use yii2rails\extension\yii\helpers\ArrayHelper;
use yii2rails\extension\yii\helpers\FileHelper;
use yubundle\storage\domain\v1\entities\FileEntity;
use yubundle\storage\domain\v1\helpers\StorageHelper;

/**
 * Class AttachmentService
 *
 * @package domain\mail\v1\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\AttachmentInterface $repository
 */
class AttachmentService extends BaseActiveService implements AttachmentInterface
{

    public function allByMailId(int $mailId)
    {
        $query = new Query;
        $query->andWhere(['mail_id' => $mailId]);
        return $this->all($query);
    }

    public function deleteById($id)
    {
        $query = new Query;
        $query->with('mail');
        /** @var AttachmentEntity $attachmentEntity */
        $attachmentEntity = $this->oneById($id, $query);
        /** @var BoxEntity $boxEntity */
        $boxEntity = \App::$domain->mail->box->oneByEmail($attachmentEntity->mail->from);
        $isSelf = \App::$domain->account->auth->identity->person_id == $boxEntity->person_id;
        if (!$isSelf) {
            throw new ForbiddenHttpException();
        }
        return parent::deleteById($id);
    }

    public function deleteAllByMailId(int $mailId)
    {
        $attachmentCollection = $this->allByMailId($mailId);
        $attachmentIdCollection = ArrayHelper::getColumn($attachmentCollection, 'id');
        $filePathCollection = array_unique(ArrayHelper::getColumn($attachmentCollection, 'path'));
        /** @var AttachmentEntity $attachment * */
        foreach ($attachmentIdCollection as $attachmentId) {
            $this->deleteById($attachmentId);
        }
        foreach ($filePathCollection as $filePath) {
            $this->deleteFromStorageByPath($filePath);
        }
    }

    protected function deleteFromStorageByPath(string $path)
    {
        $fileEntity = \App::$domain->storage->person->oneByPath($path);
        \App::$domain->storage->person->deleteById($fileEntity->id);
    }

    public function upload(UploadForm $model): AttachmentEntity
    {
        $model->validate();
        if ($model->hasErrors()) {
            throw new UnprocessableEntityHttpException($model->errors);
        }
        try {
            \App::$domain->mail->mail->oneById($model->mail_id);
        } catch (NotFoundHttpException $e) {
            $error = new ErrorCollection;
            $error->add('mail_id', 'mail/mail', 'not_found');
            throw new UnprocessableEntityHttpException($error);
        }
        $uploadedFile = StorageHelper::forgeUploaded($_FILES, 'file');
        $attachmentEntity = \App::$domain->mail->attachment->uploadOne($model->mail_id, $uploadedFile);
        return $attachmentEntity;
    }

    public function uploadAll(UploadFileCollectionForm $model, $fileEncoding) {
        $model->validate();
        if ($model->hasErrors()) {
            throw new UnprocessableEntityHttpException($model->errors);
        }
        try {
            \App::$domain->mail->mail->oneById($model->mail_id);
        } catch (NotFoundHttpException $e) {
            $error = new ErrorCollection;
            $error->add('mail_id', 'mail/mail', 'not_found');
            throw new UnprocessableEntityHttpException($error);
        }
        $serviceId = 1;
        $uploadedCollection = \App::$domain->storage->person->saveUploadedCollection( $model->files , $serviceId, $model->mail_id, $fileEncoding);
        $attachmentCollection = [];
        foreach ($uploadedCollection as $uploadedFile) {
            $attachmentCollection[] =  $this->insertFromFileEntity($uploadedFile);
        }
        return $attachmentCollection;
    }

    public function uploadOne(int $mailId, UploadedFile $uploadedFile)//: AttachmentEntity
    {
        $serviceId = 1;
        /** @var FileEntity[] $collection */
        $collection = \App::$domain->storage->person->saveUploadedCollection(['file' => $uploadedFile], $serviceId, $mailId, null);
        $fileEntity = $collection[0];
        return $this->insertFromFileEntity($fileEntity);
    }

    private function insertFromFileEntity(FileEntity $fileEntity)
    {
        $attachmentEntity = new AttachmentEntity;
        $attachmentEntity->mail_id = $fileEntity->entity_id;
        $attachmentEntity->path = $fileEntity->source_file_path;
        $attachmentEntity->file_name = $fileEntity->file_name;
        $attachmentEntity->extension = $fileEntity->extension;
        $attachmentEntity->size = $fileEntity->size;
        $attachmentEntity->status = StatusEnum::ENABLE;
        return $this->repository->insert($attachmentEntity);
    }

    public function createFromBase64(int $mailId, $images = null)
    {
        $data = $images;
        if (empty($data)) {
            $data = \Yii::$app->request->post();
            $data = ArrayHelper::getValue($data, 'file');
        }

        $uploadedCollection = [];

        if (!empty($data)) {
            foreach ($data as $fileData) {
                $fileContent = base64_decode($fileData['content']);
                $fileName = $fileData['name'];
                TempHelper::save($fileName, $fileContent);

                $uploadedFile = new UploadedFile();
                $uploadedFile->tempName = TempHelper::basePath($fileName);
                $uploadedFile->name = $fileName;
                $uploadedFile->type = FileHelper::getMimeType($uploadedFile->tempName);
                $uploadedCollection[] = $uploadedFile;

                TempHelper::createDirectoryForFile($fileName);
                TempHelper::copyUploadedToTemp($uploadedFile);
            }
            $attachments = null;
            foreach ($uploadedCollection as $uploadedFile) {
                $attachments[] = $this->uploadOne($mailId, $uploadedFile);
            }
            return $attachments;
        }
    }

    private function isUploadedFileExist(int $mailId, UploadedFile $uploadedFile)
    {
        if (file_exists($uploadedFile->tempName)) {
            $fileHash =  hash('crc32b', file_get_contents($uploadedFile->tempName));
            $fileEntity = \App::$domain->storage->file->repository->oneByServiceIdAndEntityIdAndFileHash(1, $mailId, $fileHash);
            return $this->insertFromFileEntity($fileEntity);
        } else {
            $error = new ErrorCollection;
            $error->add('file', 'Файл был загружен ранее');
            throw new UnprocessableEntityHttpException($error);
        }
    }

    public function create($data)
    {
        // TODO: Implement create() method.
    }

    public function updateById($id, $data)
    {
        // TODO: Implement updateById() method.
    }

    public function update(BaseEntity $entity)
    {
        // TODO: Implement update() method.
    }

}
