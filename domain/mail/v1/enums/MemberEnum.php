<?php

namespace domain\mail\v1\enums;

use yii2rails\extension\enum\base\BaseEnum;

class MemberEnum extends BaseEnum
{

    const CREATOR = 'creator';
    const ADMIN = 'master';
    const MEMBER = 'member';

}
