<?php

namespace domain\mail\v1\enums;

use yii2rails\extension\enum\base\BaseEnum;

class MailSocketEventEnum extends BaseEnum
{

    const INPUT_MESSAGE = 'input_message';
    const OUTPUT_MESSAGE = 'output_message';

}
