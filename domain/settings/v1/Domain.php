<?php

namespace domain\settings\v1;

use yii2rails\domain\enums\Driver;

/**
 * Class Domain
 * 
 * @package domain\settings\v1
 *
 * @property-read \domain\settings\v1\interfaces\repositories\RepositoriesInterface $repositories
 * @property-read \domain\settings\v1\interfaces\services\SystemInterface $system
 */
class Domain extends \yii2rails\domain\Domain {
	
	public function config() {
		return [
			'repositories' => [
                'system' => Driver::ENV,
			],
			'services' => [
                'system',
			],
		];
	}
	
}