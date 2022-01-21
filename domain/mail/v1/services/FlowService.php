<?php

namespace domain\mail\v1\services;

use App;
use domain\mail\v1\behaviors\MailByDataFilter;
use domain\mail\v1\behaviors\MailByDialogIdFilter;
use domain\mail\v1\behaviors\MailOnlyFilter;
use domain\mail\v1\behaviors\ModifyMessageFilter;
use domain\mail\v1\entities\BoxEntity;
use domain\mail\v1\entities\FlowEntity;
use domain\mail\v1\entities\MailEntity;
use domain\mail\v1\entities\SettingsEntity;
use domain\mail\v1\enums\FlowKindEnum;
use domain\mail\v1\enums\FolderEnum;
use domain\mail\v1\enums\MailSocketEventEnum;
use domain\mail\v1\enums\MailTypeEnum;
use domain\mail\v1\helpers\MailHelper;
use domain\mail\v1\interfaces\services\FlowInterface;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;
use yii2rails\domain\behaviors\query\QueryFilter;
use yii2rails\domain\data\Query;
use yii2rails\domain\exceptions\UnprocessableEntityHttpException;
use yii2rails\domain\services\base\BaseActiveService;
use yii2rails\extension\common\enums\StatusEnum;
use yubundle\account\domain\v2\entities\SocketEventEntity;

/**
 * Class FlowService
 *
 * @package domain\mail\v1\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\FlowInterface $repository
 */
class FlowService extends BaseActiveService implements FlowInterface
{

    public function behaviors()
    {
        $addressEntity = \App::$domain->mail->address->myAddress();
        return [
            [
                'class' => ModifyMessageFilter::class,
                'actions' => ['update'],
            ],
            [
                'class' => QueryFilter::class,
                'method' => 'with',
                'params' => 'mail.attachments',
            ],
            [
                'class' => QueryFilter::class,
                'method' => 'andWhere',
                'params' => ['mail_address' => $addressEntity->getEmail()]
            ],
            MailByDataFilter::class,
            MailOnlyFilter::class,
            MailByDialogIdFilter::class,
        ];
    }

    public function send(FlowEntity $flowEntity)
    {
        $mailEntity = parent::create($flowEntity->toArray());
        $this->sendMessageToSocket($mailEntity);
        return $mailEntity;
    }

    protected function sendMessageToSocket(FlowEntity $flowEntity)
    {
        $personId = App::$domain->mail->address->personIdByEmail($flowEntity->mail_address);
        $event = new SocketEventEntity;
        $event->name = $this->getEventNameByDirect($flowEntity->direct);
        $event->user_id = $personId;
        $flowEntity->mail = App::$domain->mail->mail->oneById($flowEntity->mail_id);
        if ($flowEntity->getDiscussionId() != null) {
            $flowEntity->type = MailTypeEnum::DISCUSSION;
        }
        $event->data = $flowEntity->toArray(['id', 'from', 'to', 'subject', 'short_content', 'type', 'dialog_id', 'discussion_id']);
        try {
            App::$domain->account->socket->sendMessage($event);
            //App::$domain->account->socketio->sendMessage($event);
        } catch (ErrorException $e) {
        }
    }

    /**
     * returns MailSocketEventEnum value by FlowKindEnum value
     *
     * @param string $direct
     * @return string
     */
    protected function getEventNameByDirect($direct)
    {
        return $direct == FlowKindEnum::OUTBOX ? MailSocketEventEnum::OUTPUT_MESSAGE : MailSocketEventEnum::INPUT_MESSAGE;
    }

    public function deleteById($id)
    {
        $this->beforeAction(self::EVENT_DELETE);
        $data = [
            'status' => StatusEnum::REJECTED,
        ];
        $entity = $this->oneById($id);
        $entity->load($data);
        $entity->validate();
        $this->repository->update($entity);
        return $this->afterAction(self::EVENT_DELETE, $entity);
    }

    public function oneById($id, Query $query = null)
    {
        $this->beforeAction(self::EVENT_VIEW);
        $flowEntity = parent::oneById($id, $query);
        return $flowEntity;
    }

    public function touch($flowIdCollection, $status)
    {
        $query = new Query();
        $query->andWhere(['id' => $flowIdCollection]);
        $flowEntityCollection = $this->repository->all($query);
        foreach ($flowEntityCollection as $flowEntity) {
            $flowEntity->seen = $status;
            $this->repository->update($flowEntity);
        }
    }

    public function move($idCollection, $folder)
    {
        FolderEnum::validate($folder);
        foreach ($idCollection as $id) {
            $flowEntity = $this->repository->oneById($id);
            $flowEntity->folder = $folder;
            parent::update($flowEntity);
        }
    }

    public function updateById($id, $data)
    {
        /** @var FlowEntity $flowEntity */
        $flowEntity = $this->repository->oneById($id);
        $mailEntity = App::$domain->mail->mail->oneById($flowEntity->mail_id);
        foreach ($data as $property => $value) {
            if ($flowEntity->hasProperty($property)) {
                $flowEntity->$property = $value;
            } elseif ($mailEntity->hasProperty($property)) {
                if ($property == 'to' || $property == 'copy_to') {
                    $mailEntity->$property = explode(',', $value);
                } else {
                    $mailEntity->$property = $value;
                }
            }
        }
        parent::update($flowEntity);
        if ($mailEntity->is_draft) {
            App::$domain->mail->mail->updateById($mailEntity->id, $data);
        }
    }

    public function createFlowByMailEntity(MailEntity $mailEntity)
    {
        $postData = \Yii::$app->request->post();
        $from = $mailEntity->from;
        $toList = $mailEntity->to;
        $copyToList = $mailEntity->copy_to;
        $blindCopyList = $mailEntity->blind_copy;
        $mailId = $mailEntity->id;
        $dialogId = ArrayHelper::getValue($postData, 'dialog_id', null);
        if (\App::$domain->mail->address->isInternal($from)) {
            $myFlowEntity = new FlowEntity;
            $myFlowEntity->mail_address = $from;
            $myFlowEntity->mail_id = $mailId;
            $myFlowEntity->direct = FlowKindEnum::OUTBOX;
            $myFlowEntity->folder = FolderEnum::OUTBOX;
            $myFlowEntity->has_attachment = $this->checkAttachments($mailId);
            $myFlowEntity->seen = true;
            $myFlowEntity->dialog_id = $dialogId;
            $this->send($myFlowEntity);
        }

        $this->createFlowList($toList, $mailId, $from, $dialogId);
        if (isset($copyToList)) {
            $this->createFlowList($copyToList, $mailId);
        }
        if (isset($blindCopyList)) {
            $this->createFlowList($blindCopyList, $mailId);
        }

        if (is_null($mailEntity->discussion_id) && $mailEntity->type != MailTypeEnum::MESSAGE) {
            self::makeAutoAnswer($mailEntity);
            $recipientEmailCollection = $this->getRecipientEmailCollection($mailEntity);
            $redirectEmailCollection = $this->getRedirectEmailCollection($recipientEmailCollection);
            \App::$domain->mail->mail->sendRedirectMessages($mailEntity, $redirectEmailCollection);
        }
    }

    public function createForwardFlow(MailEntity $mailEntity, string $forwardEmail)
    {
        $myFlowEntity = new FlowEntity;
        $myFlowEntity->mail_address = $forwardEmail;
        $myFlowEntity->mail_id = $mailEntity->id;
        $myFlowEntity->direct = FlowKindEnum::INBOX;
        $myFlowEntity->folder = FolderEnum::INBOX;
        $myFlowEntity->has_attachment = $this->checkAttachments($mailEntity->id);
        \App::$domain->mail->flow->send($myFlowEntity);
    }

    private function createFlowList(array $emails, int $mailId, $from = null, $dialogId = null)
    {
        if ($dialogId != null) {
            try {
                $dialogEntity = MailHelper::isOppositeDialogExist(['from' => $from, 'to' => $emails]);
                $dialogId = $dialogEntity->id;
            } catch (UnprocessableEntityHttpException $e) {
                $dialogId = null;
            }
        }
        $hasAttachment = $this->checkAttachments($mailId);
        foreach ($emails as $email) {
            if ($this->domain->address->isInternal($email)) {
                $opponentFlowEntity = new FlowEntity;
                $opponentFlowEntity->mail_address = $email;
                $opponentFlowEntity->mail_id = $mailId;
                $opponentFlowEntity->direct = FlowKindEnum::INBOX;
                $opponentFlowEntity->folder = FolderEnum::INBOX;
                $opponentFlowEntity->has_attachment = $hasAttachment;
                $opponentFlowEntity->dialog_id = $dialogId;
                $this->send($opponentFlowEntity);
            }
        }
    }

    private function checkAttachments($mailId)
    {
        $query = new Query();
        $query->andWhere(['mail_id' => $mailId]);
        $countAttachments = \App::$domain->mail->attachment->count($query);
        if ($countAttachments > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function makeAutoAnswer(MailEntity $mailEntity)
    {
        $from = $mailEntity->from;
        $toList = $mailEntity->to;
        $copyToList = $mailEntity->copy_to;
        $blindCopyToList = $mailEntity->blind_copy;

        if (!empty($toList)) {
            foreach ($toList as $id => $toAddress) {
                unset($toList[$id]);
                $needAnswer = MailHelper::makeAutoAnswer($toAddress, $from);
                /** @var SettingsEntity $settings */
                $settings = App::$domain->mail->settings->oneByEmail($toAddress);
                if (!is_null($settings) && $settings->is_enable_auto_answer && $needAnswer) {
                    $autoAnswerMailEntity = new MailEntity();
                    $autoAnswerMailEntity->from = $toAddress;
                    $autoAnswerMailEntity->to = $from;
                    $autoAnswerMailEntity->subject = \Yii::t('mail/mail', 'auto_answer_mail');
                    $autoAnswerMailEntity->content = $settings->auto_answer_text;
                    App::$domain->mail->mail->create($autoAnswerMailEntity->toArray());
                }
            }
        }

        if (!empty($copyToList)) {
            foreach ($copyToList as $id => $toAddress) {
                unset($copyToList[$id]);
                $needAnswer = MailHelper::makeAutoAnswer($toAddress, $from);
                /** @var SettingsEntity $settings */
                $settings = App::$domain->mail->settings->oneByEmail($toAddress);
                if (!is_null($settings) && $settings->is_enable_auto_answer && $needAnswer) {
                    $autoAnswerMailEntity = new MailEntity();
                    $autoAnswerMailEntity->from = $toAddress;
                    $autoAnswerMailEntity->to = $from;
                    $autoAnswerMailEntity->subject = \Yii::t('mail/mail', 'auto_answer_mail');
                    $autoAnswerMailEntity->content = $settings->auto_answer_text;
                    App::$domain->mail->mail->create($autoAnswerMailEntity->toArray());
                }
            }
        }

        if (!empty($blindCopyToList)) {
            foreach ($blindCopyToList as $id => $toAddress) {
                unset($blindCopyToList[$id]);
                $needAnswer = MailHelper::makeAutoAnswer($toAddress, $from);
                /** @var SettingsEntity $settings */
                $settings = App::$domain->mail->settings->oneByEmail($toAddress);
                if (!is_null($settings) && $settings->is_enable_auto_answer && $needAnswer) {
                    $autoAnswerMailEntity = new MailEntity();
                    $autoAnswerMailEntity->from = $toAddress;
                    $autoAnswerMailEntity->to = $from;
                    $autoAnswerMailEntity->subject = \Yii::t('mail/mail', 'auto_answer_mail');
                    $autoAnswerMailEntity->content = $settings->auto_answer_text;
                    App::$domain->mail->mail->create($autoAnswerMailEntity->toArray());
                }
            }
        }
    }

    private function getRecipientEmailCollection(MailEntity $mailEntity)
    {
        $recipientEmailCollection = [];
        if ($mailEntity->type == MailTypeEnum::MAIL && $mailEntity->discussion_id == null) {
            $emailCollection = [];
            if (!empty($mailEntity->to)) {
                $emailCollection = array_merge($emailCollection, $mailEntity->to);
            }
            if (!empty($mailEntity->copy_to)) {
                $emailCollection = array_merge($emailCollection, $mailEntity->copy_to);
            }
            if (!empty($mailEntity->blind_copy)) {
                $emailCollection = array_merge($emailCollection, $mailEntity->blind_copy);
            }
            foreach ($emailCollection as $email) {
                $isInternal = \App::$domain->mail->address->isInternal($email);
                if ($isInternal) {
                    $recipientEmailCollection[] = $email;
                }
            }
        }
        return $recipientEmailCollection;
    }

    private function getRedirectEmailCollection($recipientEmailCollection)
    {
        $redirectEmailCollection = [];
        foreach ($recipientEmailCollection as $recipientEmail) {
            $settings = App::$domain->mail->settings->oneByEmail($recipientEmail);
            if ($settings != null && $settings->is_enable_redirect) {
                if (!empty($settings->redirect_emails) && $settings->redirect_emails != '') {
                    $redirectEmailCollection = ArrayHelper::merge($redirectEmailCollection, explode(',', $settings->redirect_emails));
                }
            }
        }
        return $redirectEmailCollection;
    }

}
