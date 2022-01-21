<?php

namespace domain\mail\v1\repositories\ar;

use domain\mail\v1\behaviors\MailSearchBehavior;
use domain\mail\v1\interfaces\repositories\FlowInterface;
use yii2rails\extension\activeRecord\repositories\base\BaseActiveArRepository;

/**
 * Class FlowRepository
 *
 * @package domain\mail\v1\repositories\ar
 *
 * @property-read \domain\mail\v1\Domain $domain
 */
class FlowRepository extends BaseActiveArRepository implements FlowInterface
{

    protected $schemaClass = true;

    public function behaviors()
    {
        return [
            MailSearchBehavior::class
        ];
    }

    public function tableName()
    {
        return 'mail_flow';
    }

    /**
     * @return array
     *
     * @deprecated
     */

    /*public function fieldAlias()
    {
        return [
            'from' => 'sender_mail',
            'to' => 'recepient_mail',
        ];
    }*/

    public function getDataSize($boxAddress) {
        if (isset($boxAddress) && !empty($boxAddress)) {
            $flowDataSize = \Yii::$app->db->createCommand("select SUM(pg_column_size('id') + pg_column_size('direct') + pg_column_size('mail_id') + pg_column_size('mail_address') + pg_column_size('folder') + pg_column_size('has_attachment') + pg_column_size('seen') + pg_column_size('flagged') + pg_column_size('status') + pg_column_size('created_at') + pg_column_size('updated_at')) AS size from " . str_replace("_", ".", $this->tableName()) . " where mail_address = '" . strval($boxAddress) . "'")->queryAll();
            $allFlowDataSize['KB'] = round($flowDataSize[0]['size'] / 1024, 2);
            $allFlowDataSize['MB'] = round(floatval(($allFlowDataSize['KB'] / 1024)), 2);
            $result = $allFlowDataSize;
        } else {
            $result = false;
        }
        return $result;
    }

}
