<?php

namespace domain\mail\v1\services;

use App;
use domain\mail\v1\entities\FolderEntity;
use domain\mail\v1\interfaces\services\FolderInterface;
use yii2rails\domain\behaviors\query\QueryFilter;
use yii2rails\domain\data\Query;
use yii2rails\domain\services\base\BaseActiveService;
use yii2rails\extension\common\enums\StatusEnum;
use yubundle\user\domain\v1\entities\PersonEntity;

/**
 * Class FolderService
 *
 * @package domain\mail\v1\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\FolderInterface $repository
 */
class FolderService extends BaseActiveService implements FolderInterface
{

    public function behaviors()
    {
        /** @var PersonEntity $personEntity */
        $personEntity = App::$domain->user->person->oneSelf();
        return [
            [
                'class' => QueryFilter::class,
                'method' => 'orderBy',
                'params' => ['sort' => SORT_ASC]
            ],
            [
                'class' => QueryFilter::class,
                'method' => 'andWhere',
                'params' => ['status' => StatusEnum::ENABLE]
            ],
            [
                'class' => QueryFilter::class,
                'method' => 'orWhere',
                'params' => ['person_id' => [$personEntity->id, null]]
            ],
        ];
    }

    public function create($data)
    {
        $personEntity = App::$domain->user->person->oneSelf();
        $data['person_id'] = $personEntity->id;
        parent::create($data);
    }

    public function deleteById($id)
    {
        /** @var FolderEntity $folderEntity */
        $folderEntity = $this->repository->oneById($id);
        $folderEntity->status = StatusEnum::DISABLE;
        parent::update($folderEntity);
    }

    public function getByName(string $name)
    {
        $query = new Query;
        $query->andWhere(['name' => $name]);
        return parent::one($query);
    }

}
