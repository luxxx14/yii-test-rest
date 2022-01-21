<?php

namespace domain\mail\v1\services;

use App;
use domain\mail\v1\entities\DialogEntity;
use domain\mail\v1\enums\MailTypeEnum;
use domain\mail\v1\interfaces\services\DialogInterface;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii2lab\notify\domain\entities\EmailEntity;
use yii2rails\domain\behaviors\query\QueryFilter;
use yii2rails\domain\data\Query;
use yii2rails\domain\exceptions\UnprocessableEntityHttpException;
use yii2rails\domain\helpers\ErrorCollection;
use yii2rails\domain\services\base\BaseActiveService;
use yii2rails\domain\values\TimeValue;
use yii2rails\extension\common\enums\StatusEnum;
use yii2rails\extension\web\enums\HttpHeaderEnum;

/**
 * Class DialogService
 *
 * @package domain\mail\v1\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\DialogInterface $repository
 */
class DialogService extends BaseActiveService implements DialogInterface
{
    public function behaviors()
    {
        $addressEntity = \App::$domain->mail->address->myAddress();
        return [
            [
                'class' => QueryFilter::class,
                'method' => 'andWhere',
                'params' => [
                    'actor' => $addressEntity->getEmail()
                ]
            ],
            [
                'class' => QueryFilter::class,
                'method' => 'andWhere',
                'params' => [
                    '!=', 'status', StatusEnum::REJECTED,
                ]
            ],
            [
                'class' => QueryFilter::class,
                'method' => 'with',
                'params' => [
                    'box.person.worker.division',
                    'box.person.worker.post',
                ],
            ],
            [
                'class' => QueryFilter::class,
                'method' => 'orderBy',
                'params' => ['flagged' => SORT_DESC, 'updated_at' => SORT_DESC]
            ],
        ];
    }

    public function deleteMessageById($id)
    {
        $addressEntity = \App::$domain->mail->address->myAddress();
        /** @var DialogEntity $dialogEntity */
        $dialogEntity = $this->oneById($id);
        $query = new Query;
        $query->andWhere(['dialog_id' => $id, 'mail_address' => $addressEntity->email, 'status' => StatusEnum::ENABLE]);
        $flowEntities = App::$domain->mail->flow->repository->all($query);
        foreach ($flowEntities as $flowEntity) {
            $flowEntity->status = StatusEnum::REJECTED;
            App::$domain->mail->flow->update($flowEntity);
        }
    }

    public function oneById($id, Query $query = null)
    {
        /** @var DialogEntity $dialogEntity */
        $dialogEntity = $this->repository->oneById($id);
        $dialogEntity->new_message_count = 0;
        $this->repository->update($dialogEntity);
        return parent::oneById($id, $query);
    }

    /**
     * @param array $data
     * @return DialogEntity|mixed|null|\yii2rails\domain\BaseEntity
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     * @throws \yii2rails\domain\exceptions\UnprocessableEntityHttpException
     */
    public function create($data)
    {
        $addressEntity = \App::$domain->mail->address->myAddress();

        if ($addressEntity->email == $data['contractor']) {
            $error = new ErrorCollection;
            $error->add('contractor', 'mail/dialog', 'can_not_create_with_self');
            throw new UnprocessableEntityHttpException($error);
        }

        $creatorQuery = new Query;
        $creatorQuery->andWhere(['actor' => $addressEntity->email, 'contractor' => $data['contractor']]);

        if ($this->repository->count($creatorQuery) == 0) {
            if (!$this->domain->address->isInternal($data['contractor'])) {
                $error = new ErrorCollection;
                $error->add('contractor', 'mail/dialog', 'can_not_create_with_outer');
                throw new UnprocessableEntityHttpException($error);
            }
            $creatorDialogEntity = new DialogEntity;
            $creatorDialogEntity->actor = $addressEntity->email;
            $creatorDialogEntity->contractor = $data['contractor'];
            $creatorDialogEntity->validate();
            $this->repository->insert($creatorDialogEntity);
        } else {
            $creatorDialogEntity = $this->one($creatorQuery);
            Yii::$app->response->headers->add(HttpHeaderEnum::X_ENTITY_ID, $creatorDialogEntity->id);
            $error = new ErrorCollection;
            $error->add('contractor', 'mail/dialog', 'you_have_this_dialog');
            throw new UnprocessableEntityHttpException($error);
        }

        $opponentQuery = new Query;
        $opponentQuery->andWhere(['contractor' => $addressEntity->email, 'actor' => $data['contractor']]);

        if ($this->repository->count($opponentQuery) == 0) {
            $opponentDialogEntity = new DialogEntity;
            $opponentDialogEntity->actor = $data['contractor'];
            $opponentDialogEntity->contractor = $addressEntity->email;
            $this->repository->insert($opponentDialogEntity);
        }

        return $creatorDialogEntity;
    }


    /**
     * @param string $from
     * @param string $to
     * @throws \yii2rails\domain\exceptions\UnprocessableEntityHttpException
     */
    public function updateDialog(string $from, string $to)
    {
        $query = new Query;
        $query->andWhere([
            'actor' => $to,
            'contractor' => $from
        ]);
        try {
            /** @var DialogEntity $dialogEntity */
            $dialogEntity = App::$domain->mail->dialog->repository->one($query);
            $dialogEntity->new_message_count += 1;

            // TODO: Костыль
            $dialogEntity->updated_at = new TimeValue(time());

            App::$domain->mail->dialog->repository->update($dialogEntity);
        } catch (NotFoundHttpException $e) {

        }


        $query = new Query;
        $query->andWhere([
            'contractor' => $to,
            'actor' => $from
        ]);
        try {
            /** @var DialogEntity $dialogEntity */
            $dialogEntity = App::$domain->mail->dialog->repository->one($query);

            // TODO: Костыль
            $dialogEntity->updated_at = new TimeValue(time());

            App::$domain->mail->dialog->repository->update($dialogEntity);
        } catch (NotFoundHttpException $e) {

        }
    }

    public function deleteById($id)
    {
        try {
            /** @var DialogEntity $dialog */
            $dialog = $this->oneById($id);
        } catch (NotFoundHttpException $e) {
            throw new NotFoundHttpException(Yii::t('mail/dialog', 'not_found'));
        }

        $this->deleteMessageById($id);

        parent::deleteById($id);
    }

    public function touch($id) {
        $addressEntity = \App::$domain->mail->address->myAddress();
        $query = new Query();
        $query->andWhere([
            'dialog_id' => $id,
            'seen' => false,
            'mail_address' => $addressEntity->getEmail(),
        ]);
        $flowCollection = \App::$domain->mail->flow->all($query);
        $flowIdCollection = ArrayHelper::getColumn($flowCollection, 'id');
        \App::$domain->mail->flow->touch($flowIdCollection, true);
    }

}
