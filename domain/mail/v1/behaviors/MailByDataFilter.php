<?php

namespace domain\mail\v1\behaviors;

use App;
use yii\helpers\ArrayHelper;
use yii2rails\domain\behaviors\query\QueryFilter;
use yii2rails\domain\data\Query;

class MailByDataFilter extends QueryFilter
{
    const TIME_END_OF_THE_DAY = '23:59:59';
    public $actions = [];

    public function prepareQuery(Query $query)
    {
        $query = Query::forge($query);
        $date = $query->getWhere('date');
        $isDraft = $query->getWhere('is_draft');
        $query->removeWhere('date');
        if (!empty($date)) {
            $type = $date['type'];
            $dateFrom = null;
            $dateTo = null;
            if (key_exists('from', $date)) {
                $dateFrom = $date['from'];
            }
            if (key_exists('to', $date)) {
                $dateTo = $date['to'];
            }
            if ($type == 'range') {
                $query = $this->getByRange($query, $dateFrom, $dateTo);
            } else if ($type == 'day') {
                $query = $this->getByDay($query, $date['day']);
            }

            if ($isDraft) {
                $searchField = 'id';
                $mailCollection = App::$domain->mail->mail->repository->all($query);
                $mailIdList = ArrayHelper::getColumn($mailCollection, $searchField);
            } else {
                $searchField = 'mail_id';
                $flowCollection = App::$domain->mail->flow->repository->all($query);
                $mailIdList = ArrayHelper::getColumn($flowCollection, $searchField);
            }
            if (!empty($mailIdList)) {
                $query->andWhere([$searchField => $mailIdList]);
            } else {
                $query->andWhere([$searchField => null]);
            }
        }
    }

    private function getByRange(Query $query, $dateFrom = null, $dateTo = null) {
        if (!empty($dateFrom)) {
            $dateFrom = $this->convertDateToUTC0($dateFrom);
            $query->andWhere(['>=', 'updated_at', $dateFrom]);
        }
        if(!empty($dateTo)) {
            $dateToArray = explode(' ', $dateTo);
            $timeKey = 1;
            if (!key_exists($timeKey, $dateToArray)) {
                $dateTo = $dateToArray[0] . ' ' . self::TIME_END_OF_THE_DAY;
            }
            $dateTo = $this->convertDateToUTC0($dateTo);
            $query->andWhere(['<=', 'updated_at', $dateTo]);
        }
        return $query;
    }

    private function getByDay(Query $query, $day = null) {
        $dateFrom = $this->convertDateToUTC0($day);
        $dateTo = $this->convertDateToUTC0($day . ' ' . self::TIME_END_OF_THE_DAY);
        $query->andWhere(['>=', 'updated_at', $dateFrom]);
        $query->andWhere(['<=', 'updated_at', $dateTo]);
        return $query;
    }

    private function convertDateToUTC0($date) {
        $nameTimeZone = \Yii::$app->request->headers['time-zone'];
        $dateTimeZone = new \DateTimeZone($nameTimeZone);
        $dateTime = new \DateTime($date, $dateTimeZone);
        $offset = $dateTimeZone->getOffset($dateTime);
        return date ('Y-m-d H:i:s', strtotime($date) - $offset);
    }

}
