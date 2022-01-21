<?php

namespace domain\mail\v1\interfaces\services;

use yii2rails\domain\interfaces\services\CrudInterface;

/**
 * Interface FolderInterface
 *
 * @package domain\mail\v1\interfaces\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\FolderInterface $repository
 */
interface FolderInterface extends CrudInterface
{

    public function getByName(string $name);

}
