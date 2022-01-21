<?php

namespace domain\settings\v1\interfaces\services;
use yii2rails\domain\interfaces\services\ReadAllInterface;
use yii2rails\domain\interfaces\services\ReadOneInterface;

/**
 * Interface SystemInterface
 * 
 * @package domain\settings\v1\interfaces\services
 * 
 * @property-read \domain\settings\v1\Domain $domain
 * @property-read \domain\settings\v1\interfaces\repositories\SystemInterface $repository
 */
interface SystemInterface extends ReadAllInterface, ReadOneInterface {



}
