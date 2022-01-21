<?php

namespace domain\contact\v1;

use yii2rails\domain\enums\Driver;

/**
 * Class Domain
 * 
 * @package domain\contact\v1
 * @property-read \domain\contact\v1\interfaces\services\PersonalInterface $personal
 * @property-read \domain\contact\v1\interfaces\repositories\RepositoriesInterface $repositories
 *
 */
class Domain extends \yii2rails\domain\Domain {
	
	public function config() {
		return [
			'repositories' => [
                'personal' => Driver::ACTIVE_RECORD,
			],
			'services' => [
                'personal',
			],
		];
	}
	
}