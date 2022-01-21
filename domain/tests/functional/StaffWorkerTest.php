<?php

namespace tests\functional;

use App;
use yii2lab\test\helpers\DataHelper;
use yii2lab\test\Test\BaseDomainTest;
use yii2rails\domain\data\Query;

class StaffWorkerTest extends BaseDomainTest
{

    public $package = 'domain';

	public function testAll()
	{
        DataHelper::auth('admin');

        $query = new Query;
        //$query->with(['domain.company', 'person.user']);
        $dataProvider = App::$domain->staff->worker->getDataProvider($query);
        $this->assertDataProvider($dataProvider, __METHOD__);
	}

}
