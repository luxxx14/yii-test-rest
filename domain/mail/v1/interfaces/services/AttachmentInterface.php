<?php

namespace domain\mail\v1\interfaces\services;

use domain\mail\v1\entities\AttachmentEntity;
use domain\mail\v1\forms\UploadForm;
use yii\web\UploadedFile;
use yii2rails\domain\interfaces\services\CrudInterface;

/**
 * Interface AttachmentInterface
 *
 * @package domain\mail\v1\interfaces\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\AttachmentInterface $repository
 */
interface AttachmentInterface extends CrudInterface
{

    public function allByMailId(int $mailId);

    public function upload(UploadForm $model): AttachmentEntity;

    public function uploadOne(int $mailId, UploadedFile $uploadedFile);

    public function createFromBase64(int $mailId, $images = null);

}
