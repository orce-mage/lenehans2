<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Model\Process;

use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * @method self setPid(int|null $pid)
 * @method int|null getPid()
 * @method self setExportResult(string|null $exportResult)
 * @method string|null getExportResult()
 * @method self setStatus(string $status)
 * @method string getStatus()
 * @method self setEntityCode(string $code)
 * @method string getEntityCode()
 * @method self setIdentity(string $identity)
 * @method string getIdentity()
 * @method self setFinished(bool $finished)
 * @method string getFinished()
 */
class Process extends AbstractModel
{
    const ID = 'id';
    const ENTITY_CODE = 'entity_code';
    const PID = 'pid';
    const STATUS = 'status';
    const FINISHED = 'finished';
    const EXPORT_RESULT = 'export_result';
    const PROFILE_CONFIG = 'profile_config';
    const IDENTITY = 'identity';

    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Process::class);
        $this->setIdFieldName(self::ID);
    }

    public function setProfileConfig(ProfileConfigInterface $profileConfig)
    {
        return $this->setData('profile_config_model', $profileConfig);
    }

    public function getProfileConfig(): ProfileConfigInterface
    {
        return $this->_getData('profile_config_model');
    }

    public function setProfileConfigSerialized($profileConfigSerialized)
    {
        return $this->setData('profile_config', $profileConfigSerialized);
    }

    public function getProfileConfigSerialized(): string
    {
        return $this->_getData('profile_config');
    }
}
