<?php

namespace domain\mail\v1\enums;

use yii2rails\extension\enum\base\BaseEnum;

class FlowKindEnum extends BaseEnum
{

    const INBOX = 'input';
    const OUTBOX = 'output';

}
