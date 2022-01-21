<?php

namespace domain\mail\v1\interfaces\services;

use domain\mail\v1\entities\SettingsEntity;
use yii2rails\domain\interfaces\services\CrudInterface;

/**
 * Interface SettingsInterface
 *
 * @package domain\mail\v1\interfaces\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\SettingsInterface $repository
 */
interface SettingsInterface extends CrudInterface
{

    public function oneSelf();

    public function updateSelf(SettingsEntity $settingsEntity);

    public function oneByEmail(string $email);

}
