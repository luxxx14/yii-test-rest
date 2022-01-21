<?php

namespace domain\mail\v1\services;

use App;
use domain\mail\v1\entities\BoxEntity;
use domain\mail\v1\entities\SettingsEntity;
use domain\mail\v1\interfaces\services\SettingsInterface;
use yii\web\NotFoundHttpException;
use yii2rails\domain\data\Query;
use yii2rails\domain\exceptions\UnprocessableEntityHttpException;
use yii2rails\domain\helpers\ErrorCollection;
use yii2rails\domain\services\base\BaseActiveService;

/**
 * Class SettingsService
 *
 * @package domain\mail\v1\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\SettingsInterface $repository
 */
class SettingsService extends BaseActiveService implements SettingsInterface
{

    public function oneSelf()
    {
        $personId = \App::$domain->account->auth->identity->person_id;
        try {
            $settingsEntity = $this->repository->oneByPersonId($personId);
        } catch (NotFoundHttpException $e) {
            $settingsEntity = new SettingsEntity;
            $settingsEntity->person_id = $personId;
        }
        return $settingsEntity;
    }

    public function updateSelf(SettingsEntity $settingsEntity)
    {
        $personId = \App::$domain->account->auth->identity->person_id;
        $settingsEntity->person_id = $personId;
        $settingsEntity->validate();
        try {
            \App::$domain->lang->language->oneByCode($settingsEntity->language);
        } catch (NotFoundHttpException $e) {
            $error = new ErrorCollection;
            $error->add('language', 'mail/settings', 'not_found');
            throw new UnprocessableEntityHttpException($error);
        }
        $this->repository->updateOrInsert($settingsEntity);
    }

    public function create($data)
    {
        if (isset($data['redirect_emails'])) {
            $data['redirect_emails'] = json_encode(explode(',', $data['redirect_emails']));
        }
        parent::create($data);
    }

    public function updateById($id, $data)
    {
        if (isset($data['redirect_emails'])) {
            $data['redirect_emails'] = json_encode(explode(',', $data['redirect_emails']));
        }
        parent::updateById($id, $data);
    }

    public function oneByEmail(string $email)
    {
        $isInternal = \App::$domain->mail->address->isInternal($email);
        if ($isInternal) {
            try {
                /** @var BoxEntity $box */
                $box = App::$domain->mail->box->oneByEmail($email);
                $query = new Query();
                $query->andWhere(['person_id' => $box->person_id]);
                return $this->one($query);
            } catch (NotFoundHttpException $e) {
                return null;
            }
        } else {
            return null;
        }
    }

}
