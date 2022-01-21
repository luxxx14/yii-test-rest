<?php

namespace tests\functional;

use App;
use yii2lab\test\helpers\DataHelper;
use yii2lab\test\Test\BaseDomainTest;
use yii2rails\domain\data\Query;

class MailDomainTest extends BaseDomainTest
{

    public $package = 'domain';

	public function testAll()
	{
	    $query = new Query;
        $query->with('company');
        $dataProvider = App::$domain->mail->companyDomain->getDataProvider($query);
        $this->assertDataProvider($dataProvider, __METHOD__);
	}

}
