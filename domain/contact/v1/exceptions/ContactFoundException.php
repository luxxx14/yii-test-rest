<?php

namespace domain\contact\v1\exceptions;

use yii\base\Exception;

class ContactFoundException extends Exception
{

    public function getName()
    {
        return 'ContactFoundException';
    }

}
