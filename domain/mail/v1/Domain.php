<?php

namespace domain\mail\v1;

use yii2rails\domain\enums\Driver;

/**
 * Class Domain
 *
 *
 * @property-read \domain\mail\v1\interfaces\services\AddressInterface $address
 * @property-read \domain\mail\v1\interfaces\services\AttachmentInterface $attachment
 * @property-read \domain\mail\v1\interfaces\services\BoxInterface $box
 * @property-read \domain\mail\v1\interfaces\services\DialogInterface $dialog
 * @property-read \domain\mail\v1\interfaces\services\DiscussionInterface $discussion
 * @property-read \domain\mail\v1\interfaces\services\DiscussionMemberInterface $discussionMember
 * @property-read \domain\mail\v1\interfaces\services\DiscussionsMailInterface $discussionsMail
 * @property-read \domain\mail\v1\interfaces\services\DomainInterface $domain
 * @property-read \domain\mail\v1\interfaces\services\DomainInterface $companyDomain
 * @property-read \domain\mail\v1\interfaces\services\FlowInterface $flow
 * @property-read \domain\mail\v1\interfaces\services\FolderInterface $folder
 * @property-read \domain\mail\v1\interfaces\services\MailInterface $mail
 * @property-read \domain\mail\v1\interfaces\repositories\RepositoriesInterface $repositories
 * @property-read \domain\mail\v1\interfaces\services\SettingsInterface $settings
 */
class Domain extends \yii2rails\domain\Domain
{

    public function config()
    {
        return [
            'container' => [
                'domain\mail\v1\services\CompanyDomainService' => 'domain\mail\v1\services\DomainService',
                'domain\mail\v1\repositories\ar\CompanyDomainRepository' => 'domain\mail\v1\repositories\ar\DomainRepository',
                'domain\mail\v1\repositories\schema\CompanyDomainSchema' => 'domain\mail\v1\repositories\schema\DomainSchema',
                'domain\mail\v1\entities\CompanyDomainEntity' => 'domain\mail\v1\entities\DomainEntity',
            ],
            'repositories' => [
                'attachment' => Driver::ACTIVE_RECORD,
                'box' => Driver::ACTIVE_RECORD,
                'discussion' => Driver::ACTIVE_RECORD,
                'discussionMember' => Driver::ACTIVE_RECORD,
                'discussionsMail' => Driver::ACTIVE_RECORD,
                'companyDomain' => Driver::ACTIVE_RECORD,
                'mail' => Driver::ACTIVE_RECORD,
                'flow' => Driver::ACTIVE_RECORD,
                'dialog' => Driver::ACTIVE_RECORD,
                'folder' => Driver::ACTIVE_RECORD,
                'settings' => Driver::ACTIVE_RECORD,
            ],
            'services' => [
                'attachment',
                'box',
                'discussion',
                'discussionMember',
                'discussionsMail',
                'companyDomain',
                'mail',
                'flow',
                'address',
                'dialog',
                'folder',
                'settings',
                'report',
            ],
        ];
    }

}