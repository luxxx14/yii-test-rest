<?php

namespace domain\mail\v1\interfaces\repositories;

use domain\mail\v1\entities\SettingsEntity;
use yii2rails\domain\data\Query;
use yii2rails\domain\interfaces\repositories\CrudInterface;

/**
 * Interface SettingsInterface
 *
 * @package domain\mail\v1\interfaces\repositories
 *
 * @property-read \domain\mail\v1\Domain $domain
 */
interface SettingsInterface extends CrudInterface
{

    /**
     * @param int $personId
     * @param Query|null $query
     * @return SettingsEntity
     * @throws \yii\web\NotFoundHttpException
     */
    public function oneByPersonId(int $personId, Query $query = null);

}
