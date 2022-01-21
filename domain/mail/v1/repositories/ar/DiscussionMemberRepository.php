<?php

namespace domain\mail\v1\repositories\ar;


use domain\mail\v1\interfaces\repositories\DiscussionMemberInterface;
use yii2rails\extension\activeRecord\repositories\base\BaseActiveArRepository;

/**
 * Class DiscussionMemberRepository
 *
 * @package domain\mail\v1\repositories\ar
 *
 * @property-read \domain\mail\v1\Domain $domain
 */
class DiscussionMemberRepository extends BaseActiveArRepository implements DiscussionMemberInterface
{

    protected $schemaClass = true;

    public function tableName()
    {
        return 'mail_discussion_member';
    }

}
