<?php

namespace domain\mail\v1\enums;

use yii2rails\extension\enum\base\BaseEnum;

class MailTypeEnum extends BaseEnum
{

    const MAIL = 'mail';
    const MESSAGE = 'message';
    const DISCUSSION = 'discussion-message';
}
