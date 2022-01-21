<?php

namespace tests\functional;

use yii2lab\test\Test\BaseActiveDomainTest;

class MailBoxTest extends BaseActiveDomainTest
{

    public $package = 'domain';

    public function relations() {
        return [
            'domain.company',
            'person.user',
        ];
    }

    public function authBy() {
        return 'admin';
    }

    public function service() {
        return 'mail.box';
    }

}
