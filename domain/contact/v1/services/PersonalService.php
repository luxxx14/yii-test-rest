<?php

namespace domain\contact\v1\services;

use App;
use domain\contact\v1\entities\PersonalEntity;
use domain\contact\v1\enums\ContactKindEnum;
use domain\contact\v1\interfaces\services\PersonalInterface;
use domain\mail\v1\entities\MailEntity;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use yii2rails\domain\behaviors\entity\CheckOwnerIdFilter;
use yii2rails\domain\behaviors\query\CurrentUserOnlyFilter;
use yii2rails\domain\exceptions\BadQueryHttpException;
use yii2rails\domain\exceptions\UnprocessableEntityHttpException;
use yii2rails\domain\helpers\ErrorCollection;
use yii2rails\domain\services\base\BaseActiveService;
use yii2rails\extension\yii\helpers\ArrayHelper;
use yii2rails\domain\data\Query;
use domain\contact\v1\exceptions\ContactFoundException;
use yubundle\staff\domain\v1\entities\WorkerEntity;

/**
 * Class PersonalService
 *
 * @package domain\contact\v1\services
 *
 * @property-read \domain\contact\v1\Domain $domain
 * @property-read \domain\contact\v1\interfaces\repositories\PersonalInterface $repository
 */
class PersonalService extends BaseActiveService implements PersonalInterface
{

    public function behaviors()
    {
        return [
            [
                'class' => CurrentUserOnlyFilter::class, // выбирать только свои пункты
                'attribute' => 'person_id',
                'fromIdentityAttribute' => 'person_id',
            ],
            [
                'class' => CheckOwnerIdFilter::class, // если не своя сущность, то 403
                'attribute' => 'person_id',
                'fromIdentityAttribute' => 'person_id',
            ],
        ];
    }

    public function updateById($id, $data)
    {
        $personalEntity = $this->oneById($id);
        $personalEntity->validate();
        if (isset($personalEntity->email) && $personalEntity->email !== '') {
            $personalEntity->kind = $this->checkEmailKind($personalEntity->email);
        }
        $personalEntity = new PersonalEntity(ArrayHelper::merge($personalEntity->toArray(), $data));
        $personalEntity->validate();
        return parent::updateById($id, $personalEntity);
    }

    public function create($data)
    {
        $personEntity = new PersonalEntity($data);
        $personEntity->validate();
        if (isset($data['email']) && !is_null($data['email'])  && $data['email'] != '') {
            $personId = \App::$domain->account->auth->identity->person_id;

            $query = Query::forge();
            $query->select('*');
            $query->orWhere(['email' => $data['email']]);
            $query->andWhere(['person_id' => $personId]);

            $contactFound = \App::$domain->contact->personal->repository->all($query);
            if (isset($contactFound) && !empty($contactFound) && count($contactFound) > 0) {
                $error = new ErrorCollection;
                $error->add('email', \Yii::t('contact/personal' ,'contact_already_exist'));
                throw new UnprocessableEntityHttpException($error);
            }
        }
        if (isset($personEntity->email) && $personEntity->email != '') {
            $personEntity->kind = $this->checkEmailKind($personEntity->email);
        }
        return parent::create($personEntity);
    }

    public function allRecent(Query $query = null)
    {

        $contactList = [];

        $myMail = \App::$domain->mail->address->myAddress()->getEmail();

        $query = Query::forge($query);
        $searchEmail = $query->getWhere('email');

        if (strlen($searchEmail) < 3) {
            $error = new ErrorCollection;
            $error->add('email', 'contact/personal', 'too_few_characters');
            throw new UnprocessableEntityHttpException($error);
        }

        $query = new Query();
        $query->andWhere([
            'or',
            ['from' => $myMail],
            new Expression('lower(cast("to" as varchar)) ilike \'%' . $myMail. '%\''),
            new Expression('lower(cast("copy_to" as varchar)) ilike \'%' . $myMail. '%\''),
        ]);
        $mailCollection = \App::$domain->mail->mail->all($query);

        $addressCollection = [];
        /** @var MailEntity $mail */
        foreach ($mailCollection as $mail) {
            if (is_array($mail->to)) {
                $addressCollection = array_merge($addressCollection, [$mail->from], $mail->to);
            } else {
                $addressCollection = array_merge($addressCollection, [$mail->from]);
            }
        }
        $recentContactList = array_count_values($addressCollection);
        unset($recentContactList[$myMail]);
        $recentContactList = array_keys($recentContactList);

        $query = new Query;
        $query->andWhere(new Expression('email ilike \'%' . $searchEmail. '%\''));
        $selfContactCollection = App::$domain->contact->personal->all($query);
        $selfContactList = ArrayHelper::getColumn($selfContactCollection, 'email');

        $recentContactList = ArrayHelper::merge($recentContactList, $selfContactList);

        $query = new Query;
        $query->andWhere(new Expression('email ilike \'%' . $searchEmail. '%\''));
        $workerCollection = App::$domain->staff->worker->all($query);
        $workerEmailList = ArrayHelper::getColumn($workerCollection, 'email');

        $recentContactList = ArrayHelper::merge($recentContactList, $workerEmailList);

        $recentContactList = array_unique($recentContactList);

        foreach ($recentContactList as $id => $contact) {
            $login = stristr($contact, '@', true);
            if (empty($login) || strpos($login, $searchEmail) === false) {
                unset($recentContactList[$id]);
            }
        }

        array_splice($recentContactList, 7);

        foreach ($recentContactList as $id => $recentContact) {
            try {
                $query = new Query();
                $query->andWhere(['email' => $recentContact]);
                $contact = \App::$domain->contact->personal->one($query);
            } catch (NotFoundHttpException $e) {
                try {
                    $query = new Query;
                    $query->andWhere(['email' => $recentContact]);
                    /** @var WorkerEntity $workerEntity */
                    $workerEntity = App::$domain->staff->worker->one($query);
                    $contact = new PersonalEntity();
                    $contact->email = $recentContact;
                    $contact->first_name = $workerEntity->person->first_name;
                    $contact->last_name = $workerEntity->person->last_name;
                    $contact->middle_name = $workerEntity->person->middle_name;
                    $contact->phone = $workerEntity->phone;
                } catch (NotFoundHttpException $e) {
                    $contact = new PersonalEntity();
                    $contact->email = $recentContact;
                }

            }
            $contactList[] = $contact;
        }
        return $contactList;
    }

    private function checkEmailKind($email)
    {
        try {
            App::$domain->mail->box->oneByEmail($email);
            $kind = ContactKindEnum::INNER;
        } catch (NotFoundHttpException $e) {
            $kind = ContactKindEnum::OUTER;
        }
        return $kind;
    }

}