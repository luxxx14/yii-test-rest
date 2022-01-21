<?php

namespace domain\mail\v1\repositories\ar;

use domain\mail\v1\interfaces\repositories\MailInterface;
use yii2rails\domain\behaviors\query\SearchFilter;
use yii2rails\extension\activeRecord\repositories\base\BaseActiveArRepository;

use yii2rails\domain\data\Query;

/**
 * Class MailRepository
 *
 * @package domain\mail\v1\repositories\ar
 *
 * @property-read \domain\mail\v1\Domain $domain
 */
class MailRepository extends BaseActiveArRepository implements MailInterface
{

    protected $schemaClass = true;

    public function tableName()
    {
        return 'mail_mail';
    }

    public function behaviors()
    {
        return [
            [
                'class' => SearchFilter::class,
                'fields' => [
                    'from',
                    'to',
                    'subject',
                    'content',
                ],
                /*'virtualFields' => [
                    'text' => [
                        'to',
                        'subject',
                        'content',
                    ],
                ],*/
            ],
        ];
    }

    public function getDataSize($boxAddress) {
        if (isset($boxAddress) && !empty($boxAddress)) {
            $query = Query::forge();
            $query->select(["mail_id"]);
            $query->where(['mail_address' => $boxAddress]);
            $myAllMailsList = \App::$domain->mail->flow->repository->all($query);
            if (!empty($myAllMailsList)) {
                foreach ($myAllMailsList as $key => $value) {
                    $mailsSize = \Yii::$app->db->createCommand("select SUM(pg_column_size('id') + pg_column_size('kind') + pg_column_size('reply_from') + pg_column_size('from') + pg_column_size('to') + pg_column_size('copy_to') + pg_column_size('subject') + pg_column_size('content') + pg_column_size('status') + pg_column_size('discussion_id') + pg_column_size('type') + pg_column_size('is_draft') + pg_column_size('created_at') + pg_column_size('updated_at')) AS size from " . str_replace("_", ".", $this->tableName()) . " where id = " . $value->mail_id)->queryAll();
                    $mailsSizes[] = $mailsSize[0]['size'];
                }
                $allMailDataSize['KB'] = round(array_sum($mailsSizes) / 1024, 2);
                $allMailDataSize['MB'] = round(floatval(($allMailDataSize['KB'] / 1024)), 2);
            } else {
                $allMailDataSize['KB'] = 0;
                $allMailDataSize['MB'] = 0;
            }
            $result = $allMailDataSize;
        } else {
            $result = false;
        }

        return $result;
    }

}
