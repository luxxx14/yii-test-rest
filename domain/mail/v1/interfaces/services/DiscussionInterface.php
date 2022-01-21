<?php

namespace domain\mail\v1\interfaces\services;

use yii2rails\domain\interfaces\services\CrudInterface;

/**
 * Interface DiscussionInterface
 *
 * @package domain\mail\v1\interfaces\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\DiscussionInterface $repository
 */
interface DiscussionInterface extends CrudInterface
{

    public function getBySubjectAndEmails(string $subject, array $emails);

    public function deleteMessageById($id);

}
