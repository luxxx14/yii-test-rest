<?php

namespace tests\functional;

use App;
use yii2lab\test\helpers\DataHelper;
use yii2lab\test\Test\BaseDomainTest;
use yii2rails\domain\data\Query;

class StaffCompanyTest extends BaseDomainTest
{

    public $package = 'domain';

	public function testAll()
	{
        DataHelper::auth('admin');
        $query = new Query;
        $query->with(['domains']);
        $dataProvider = App::$domain->staff->company->getDataProvider($query);
        $this->assertDataProvider($dataProvider, __METHOD__);
	}

}
