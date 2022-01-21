<?php

namespace tests\functional;

use App;
use domain\mail\v1\entities\MailEntity;
use domain\mail\v1\enums\MailKindEnum;
use yii2lab\test\helpers\DataHelper;
use yii2lab\test\Test\BaseDomainTest;
use yii2rails\domain\data\Query;

class MailSendTest extends BaseDomainTest
{

    public $package = 'domain';

    private function clearRepositories()
    {
        App::$domain->mail->mail->repository->truncate();
        App::$domain->mail->flow->repository->truncate();
        App::$domain->mail->dialog->repository->truncate();
    }

    private function send()
    {
        DataHelper::auth('admin');

        $mailEntity = new MailEntity;
        $mailEntity->kind = MailKindEnum::OUTER;
        $mailEntity->to = 'tester1@yuert.kz';
        $mailEntity->subject = 'subject admin > tester1 (autotest)';
        $mailEntity->content = 'content admin > tester1 (autotest)';
        App::$domain->mail->mail->create($mailEntity->toArray());

        DataHelper::auth('tester1');

        $mailEntity = new MailEntity;
        $mailEntity->kind = MailKindEnum::OUTER;
        $mailEntity->to = 'admin@yuert.kz';
        $mailEntity->subject = 'subject tester1 > admin (autotest)';
        $mailEntity->content = 'content tester1 > admin (autotest)';
        App::$domain->mail->mail->create($mailEntity->toArray());
    }

    private function assertTester1Data()
    {
        DataHelper::auth('tester1');

        $query = new Query;
        $query->perPage(50);
        $query->with('mail');
        $flowDataProvider = App::$domain->mail->flow->repository->getDataProvider($query);
        DataHelper::fakeCollectionValue($flowDataProvider->getModels(), [
            'read_at' => '2019-02-22 17:25:10',
            'created_at' => '2019-02-22 17:25:10',
            'updated_at' => '2019-02-22 17:25:10',
        ]);
        $this->assertDataProvider($flowDataProvider, __METHOD__ . 'Flow');

        $query = new Query;
        $query->perPage(50);
        $dataProvider = App::$domain->mail->mail->repository->getDataProvider($query);
        DataHelper::fakeCollectionValue($dataProvider->getModels(), [
            'created_at' => '2019-02-22 17:25:10',
            'updated_at' => '2019-02-22 17:25:10',
        ]);
        $this->assertDataProvider($dataProvider, __METHOD__ . 'Mail');

        $query = new Query;
        $query->perPage(50);
        $dataProvider = App::$domain->mail->dialog->repository->getDataProvider($query);
        DataHelper::fakeCollectionValue($dataProvider->getModels(), [
            'created_at' => '2019-02-22 17:25:10',
            'updated_at' => '2019-02-22 17:25:10',
        ]);
        $this->assertDataProvider($dataProvider, __METHOD__ . 'Dialog');
    }

    private function assertAdminData()
    {
        DataHelper::auth('admin');

        $query = new Query;
        $query->perPage(50);
        $query->with('mail');
        $flowDataProvider = App::$domain->mail->flow->repository->getDataProvider($query);
        DataHelper::fakeCollectionValue($flowDataProvider->getModels(), [
            'read_at' => '2019-02-22 17:25:10',
            'created_at' => '2019-02-22 17:25:10',
            'updated_at' => '2019-02-22 17:25:10',
        ]);
        $this->assertDataProvider($flowDataProvider, __METHOD__ . 'Flow');

        $query = new Query;
        $query->perPage(50);
        $dataProvider = App::$domain->mail->mail->repository->getDataProvider($query);
        DataHelper::fakeCollectionValue($dataProvider->getModels(), [
            'created_at' => '2019-02-22 17:25:10',
            'updated_at' => '2019-02-22 17:25:10',
        ]);
        $this->assertDataProvider($dataProvider, __METHOD__ . 'Mail');

        $query = new Query;
        $query->perPage(50);
        $dataProvider = App::$domain->mail->dialog->repository->getDataProvider($query);
        DataHelper::fakeCollectionValue($dataProvider->getModels(), [
            'created_at' => '2019-02-22 17:25:10',
            'updated_at' => '2019-02-22 17:25:10',
        ]);
        $this->assertDataProvider($dataProvider, __METHOD__ . 'Dialog');
    }

    public function testSendMessage()
    {
        $this->clearRepositories();
        $this->send();
        $this->assertTester1Data();
        $this->assertAdminData();
    }

}
