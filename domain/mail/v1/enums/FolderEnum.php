<?php

namespace domain\mail\v1\enums;

use yii2rails\extension\enum\base\BaseEnum;

class FolderEnum extends BaseEnum
{

    const SPAM = 'spam';
    const ARCHIVE = 'archive';
    const TRASH = 'trash';
    const INBOX = 'inbox';
    const OUTBOX = 'outbox';

}
