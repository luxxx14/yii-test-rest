<?php

namespace domain\settings\v1\interfaces\repositories;
use yii2rails\domain\interfaces\repositories\ReadAllInterface;
use yii2rails\domain\interfaces\repositories\ReadOneInterface;

/**
 * Interface SystemInterface
 * 
 * @package domain\settings\v1\interfaces\repositories
 * 
 * @property-read \domain\settings\v1\Domain $domain
 */
interface SystemInterface extends ReadAllInterface, ReadOneInterface {

}
