<?php

namespace domain\mail\v1\repositories\ar;

use domain\mail\v1\interfaces\repositories\SettingsInterface;
use yii2rails\domain\data\Query;
use yii2rails\extension\activeRecord\repositories\base\BaseActiveArRepository;

/**
 * Class SettingsRepository
 *
 * @package domain\mail\v1\repositories\ar
 *
 * @property-read \domain\mail\v1\Domain $domain
 */
class SettingsRepository extends BaseActiveArRepository implements SettingsInterface
{

    protected $schemaClass = true;
    protected $primaryKey = 'person_id';

    public function tableName()
    {
        return 'mail_settings';
    }

    public function oneByPersonId(int $personId, Query $query = null)
    {
        $query = Query::forge($query);
        $query->andWhere(['person_id' => $personId]);
        return $this->one($query);
    }

}
