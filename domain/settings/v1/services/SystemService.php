<?php

namespace domain\settings\v1\services;

use domain\settings\v1\interfaces\services\SystemInterface;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii2rails\app\domain\helpers\EnvService;
use yii2rails\domain\BaseEntity;
use yii2rails\domain\data\Query;
use yii2rails\domain\services\base\BaseService;
use yii2rails\domain\values\NullValue;

/**
 * Class SystemService
 * 
 * @package domain\settings\v1\services
 * 
 * @property-read \domain\settings\v1\Domain $domain
 * @property-read \domain\settings\v1\interfaces\repositories\SystemInterface $repository
 */
class SystemService extends BaseService implements SystemInterface {

    public $configKeys = [
        'account'
    ];
    public $defaultConfig = [
        'account' => [
            'registration' => [
                'access' => 'open', // доступность регистрации (open,invite,close)
                'defaultStatus' => 1, // начальный статус пользователя (если 0, то только по премодерации)
            ],
        ],
    ];

    public function all(Query $query = null)
    {
        $all = $this->repository->all($query);
        $all = ArrayHelper::merge($this->defaultConfig, $all);
        $all = ArrayHelper::filter($all, $this->configKeys);
        return $all;
    }

    public function count(Query $query = null)
    {
        return $this->repository->count($query);
    }

    public function oneById($id, Query $query = null)
    {
        $all = $this->all();
        $nullValue = new NullValue;
        $node = ArrayHelper::getValue($all, $id, $nullValue);
        if($node instanceof NullValue) {
            throw new NotFoundHttpException('Config not found!');
        }
        return $node;
    }

    public function isExistsById($id)
    {
        // TODO: Implement isExistsById() method.
    }

    public function isExists($condition)
    {
        // TODO: Implement isExists() method.
    }

    public function one(Query $query = null)
    {
        // TODO: Implement one() method.
    }

}
