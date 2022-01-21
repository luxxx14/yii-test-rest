<?php

namespace domain\mail\v1\repositories\ar;

use domain\mail\v1\interfaces\repositories\FolderInterface;
use yii2rails\extension\activeRecord\repositories\base\BaseActiveArRepository;

/**
 * Class FolderRepository
 *
 * @package domain\mail\v1\repositories\ar
 *
 * @property-read \domain\mail\v1\Domain $domain
 */
class FolderRepository extends BaseActiveArRepository implements FolderInterface
{

    protected $schemaClass = true;

    public function tableName()
    {
        return 'mail_folder';
    }

}
