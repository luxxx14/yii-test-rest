<?php

namespace backend\modules\mail\helpers;

use yii2rails\extension\menu\interfaces\MenuInterface;

class Menu implements MenuInterface {
	
	public function toArray() {
		return [
            'label' => ['mail/mail', 'menu_title'],
            'module' => 'staff',
            //'icon' => 'sliders',
            'items' => [
                [
                    'label' => ['mail/domain', 'title'],
                    'url' => 'mail/domain',
                ],
            ],
		];
	}
}
