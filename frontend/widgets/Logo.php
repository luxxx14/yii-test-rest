<?php

namespace frontend\widgets;

use yii\base\Widget;

class Logo extends Widget
{

    public $width = 180;

	public function run() {
		echo '<img src="https://yuwert.kz/images/logo.svg" style="width: ' . $this->width . 'px;">';
	}

}
