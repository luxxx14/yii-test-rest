<?php

namespace domain\mail\v1\repositories\ar;

use App;
use domain\mail\v1\behaviors\DialogSearchBehavior;
use domain\mail\v1\entities\DialogEntity;
use domain\mail\v1\interfaces\repositories\DialogInterface;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii2rails\domain\behaviors\query\SearchFilter;
use yii2rails\domain\data\Query;
use yii2rails\extension\activeRecord\repositories\base\BaseActiveArRepository;

/**
 * Class DialogRepository
 *
 * @package domain\mail\v1\repositories\ar
 *
 * @property-read \domain\mail\v1\Domain $domain
 */
class DialogRepository extends BaseActiveArRepository implements DialogInterface
{

    protected $schemaClass = true;

    public function tableName()
    {
        return 'mail_dialog';
    }

    public function behaviors()
    {
        return [
            DialogSearchBehavior::class,
        ];
    }

}
