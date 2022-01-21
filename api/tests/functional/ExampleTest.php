<?php

namespace tests\functional;

use yii2lab\notify\domain\helpers\test\NotifyTestHelper;
use yii2lab\rest\domain\entities\RequestEntity;
use yii2tool\test\enums\TypeEnum;
use yii2tool\test\helpers\CurrentIdTestHelper;
use yii2tool\test\helpers\TestHelper;
use yii2tool\test\Test\BaseActiveApiTest;
use yii2rails\extension\web\enums\HttpMethodEnum;
use yii2rails\extension\yii\helpers\FileHelper;
use yii2bundle\account\domain\v3\helpers\test\RegistrationTestHelper;

/*
Configure env-local.php:

'test' => [
    'mode' => 'dev', // режим сервера: dev, test, prod
    'skipBug' => true, // скрывать кейсы, помеченные как баг
    'server' => [
        'dev' => [
            'mailDomainCorparate' => 'yuweb.kz',
            'url' => [
                'api' => 'http://api.yumail.project',
            ],
            'mailServer' => [
                'token' => 'jwt eyJ0eXAkCg ... owUy0UiukY',
            ],
        ],
        'test' => [
            'mailDomainCorparate' => 'yuweb.kz',
            'url' => [
                'api' => 'https://test-api.yumail.kz',
            ],
            'accountManager' => [
                'login' => '',
                'password' => '',
            ],
            'mailServer' => [
                'token' => 'jwt eyJ0eXAkCg ... owUy0UiukY',
            ],
        ],
        'prod' => [
            'mailDomainCorparate' => 'yuweb.kz',
            'url' => [
                'api' => 'https://api.yumail.kz',
            ],
            'accountManager' => [
                'login' => '',
                'password' => '',
            ],
            'mailServer' => [
                'token' => '',
            ],
        ],
        'ttc' => [
            'mailDomainCorparate' => 'tcloud.yumail.kz',
            'url' => [
                'api' => 'https://api.tcloud.yumail.kz',
            ],
            'mailServer' => [
                'token' => '',
            ],
        ],
    ],
    'mail' => [
        'pop3' => [
            'host' => 'pop.mail.ru',
            'username' => '',
            'password' => '',
        ],
        'smtp' => [
            'host' => 'ssl://smtp.mail.ru',
            'username' => '',
            'password' => '',
            'port' => '465',
        ],
    ],
],

*/

class ExampleTest extends BaseActiveApiTest
{

    private $exampleSchema = [
        'id' => TypeEnum::INTEGER,
    ];

    public function _testLoadFixture()
    {
        TestHelper::copySqlite(FileHelper::up(__DIR__), false);
    }

    public function _testClearSms()
    {
        NotifyTestHelper::cleanSms();
    }

    public function _testCreateUser()
    {
        RegistrationTestHelper::registration();
    }

    public function _testCreate() {
        $this->authByNewUser();
        $this->createEntity('example', [
            'param1' => 'value1',
            'param2' => 'value2',
        ], true);
    }

    public function _testCreateFail() {
        $this->authByNewUser();
        $this->createEntityUnProcessible('example', [
            'param1' => 'value1',
            'param2' => 'value2',
        ], ['param1']);
    }

    public function _testAll()
    {
        $this->authByNewUser();
        $actual = $this->readCollection('example', [], $this->exampleSchema, 1);
    }

    public function _testView()
    {
        $id = CurrentIdTestHelper::get();
        $this->authByNewUser();
        $actual = $this->readEntity('example', $id, $this->exampleSchema);
        $this->tester->assertArraySubset([
            'param1' => 'value1',
        ], $actual);

        $this->tester->assertValueType(TypeEnum::STRING, $actual['field1']);
        $this->tester->assertArrayType(UserSchema::$person, $actual['field2']);
    }

    public function _testViewNotFound()
    {
        $id = CurrentIdTestHelper::get();
        $this->authByNewUser();
        $this->readNotFoundEntity('example', $id);
    }

    public function _testUpdate()
    {
        $id = CurrentIdTestHelper::get();
        $this->authByNewUser();
        $this->updateEntity('example', $id, [
            'param1' => 'value1',
            'param2' => 'value2',
        ]);
    }

    public function _testDelete()
    {
        $id = CurrentIdTestHelper::get();
        $this->authByNewUser();
        $this->deleteEntity('example', $id);
    }

    public function _testCustomCreate()
    {
        $this->authByNewUser();
        $requestEntity = new RequestEntity;
        $requestEntity->uri = 'example';
        $requestEntity->method = HttpMethodEnum::POST;
        $requestEntity->data = [
            'param1' => 'value1',
        ];
        $responseEntity = $this->sendRequest($requestEntity);
        $this->tester->assertEquals(201, $responseEntity->status_code);
    }

    public function _testSkipBug()
    {
        if(TestHelper::isSkipBug()) return; // если всключен тихий режим, то код ниже не будет выполнен
    }

    public function _testPrintMessage()
    {
        TestHelper::printMessage('вывести сообщение');
    }

    public function _testRelation()
    {
        $this->assertRelationContract('example', 13, ['parent' => $this->exampleSchema]);
    }

    public function _testCustomRequest()
    {
        $responseEntity = $this->send('example', HttpMethodEnum::POST, [
            'name' => 'qwerty',
        ]);
    }

    public function _testCustomRequest2()
    {
        $requestEntity = new RequestEntity;
        $requestEntity->uri = 'example';
        $requestEntity->method = HttpMethodEnum::POST;
        $requestEntity->data = [
            'name' => 'qwerty',
        ];
        $responseEntity = $this->sendRequest($requestEntity);
    }
}
