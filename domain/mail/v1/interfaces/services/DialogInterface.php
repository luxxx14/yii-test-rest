<?php

namespace domain\mail\v1\interfaces\services;

use yii2rails\domain\interfaces\services\CrudInterface;

/**
 * Interface DialogInterface
 *
 * @package domain\mail\v1\interfaces\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\DialogInterface $repository
 */
interface DialogInterface extends CrudInterface
{

    public function updateDialog(string $from, string $to);

    public function deleteMessageById(int $dialogId);

}
