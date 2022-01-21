<?php

namespace domain\mail\v1\interfaces\services;

use yii2rails\domain\data\Query;
use yii2rails\domain\interfaces\services\CrudInterface;

/**
 * Interface DiscussionMemberInterface
 *
 * @package domain\mail\v1\interfaces\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\DiscussionMemberInterface $repository
 */
interface DiscussionMemberInterface extends CrudInterface
{

    public function allBySelf(Query $query = null);

}
