<?php

namespace tests\functional;

use App;
use yii2lab\test\helpers\DataHelper;
use yii2lab\test\Test\BaseDomainTest;
use yii2rails\domain\data\Query;

class StaffDivisionTest extends BaseDomainTest
{

    public $package = 'domain';

	public function testAll()
	{
        DataHelper::auth('admin');
        $query = new Query;
        $query->with(['parent', 'child', 'company']);
        $dataProvider = App::$domain->staff->division->getDataProvider($query);
        $this->assertDataProvider($dataProvider, __METHOD__);
	}

    public function testDeepTree()
    {
        DataHelper::auth('admin');
        $query = new Query;
        $query->with(['child.child.child']);
        $query->andWhere(['parent_id' => null]);
        $dataProvider = App::$domain->staff->division->getDataProvider($query);
        $this->assertDataProvider($dataProvider, __METHOD__);
    }

    public function testTree()
    {
        DataHelper::auth('admin');
        $query = new Query;
        $dataProvider = App::$domain->staff->division->tree($query);
        $this->assertDataProvider($dataProvider, __METHOD__);
    }

}
