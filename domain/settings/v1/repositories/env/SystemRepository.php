<?php

namespace domain\settings\v1\repositories\env;

use domain\settings\v1\entities\SystemEntity;
use domain\settings\v1\interfaces\repositories\SystemInterface;
use yii\helpers\ArrayHelper;
use yii2rails\app\domain\helpers\EnvService;
use yii2rails\domain\data\Query;
use yii2rails\domain\repositories\BaseRepository;

/**
 * Class SystemRepository
 * 
 * @package domain\settings\v1\repositories\env
 * 
 * @property-read \domain\settings\v1\Domain $domain
 */
class SystemRepository extends BaseRepository implements SystemInterface {

    public function all(Query $query = null)
    {
        $config = EnvService::get(null);
        return $config;
    }

    public function count(Query $query = null)
    {
        return count($this->all($query));
    }

    public function oneById($id, Query $query = null)
    {
        $all = $this->all();
        return ArrayHelper::getValue($all, $id);
    }
}
