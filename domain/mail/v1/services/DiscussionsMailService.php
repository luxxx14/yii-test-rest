<?php

namespace domain\mail\v1\services;

use domain\mail\v1\interfaces\services\DiscussionsMailInterface;
use yii2rails\domain\services\base\BaseActiveService;

/**
 * Class DiscussionsMailService
 *
 * @package domain\mail\v1\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\DiscussionsMailInterface $repository
 */
class DiscussionsMailService extends BaseActiveService implements DiscussionsMailInterface
{

}
