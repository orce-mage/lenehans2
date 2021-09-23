<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Code;

use Amasty\GiftCard\Api\Data\CodeInterface;
use Magento\Framework\Model\AbstractModel;

class Code extends AbstractModel implements CodeInterface
{
    public function _construct()
    {
        $this->_init(ResourceModel\Code::class);
        $this->setIdFieldName(CodeInterface::CODE_ID);
    }

    public function setCodeId(int $id): CodeInterface
    {
        return $this->setData(CodeInterface::CODE_ID, $id);
    }

    public function getCodeId(): int
    {
        return (int)$this->_getData(CodeInterface::CODE_ID);
    }

    public function setCode(string $code): CodeInterface
    {
        return $this->setData(CodeInterface::CODE, $code);
    }

    public function getCode(): string
    {
        return $this->_getData(CodeInterface::CODE);
    }

    public function setCodePoolId(int $id): CodeInterface
    {
        return $this->setData(CodeInterface::CODE_POOL_ID, $id);
    }

    public function getCodePoolId(): int
    {
        return (int)$this->_getData(CodeInterface::CODE_POOL_ID);
    }

    public function setStatus(int $status): CodeInterface
    {
        return $this->setData(CodeInterface::STATUS, $status);
    }

    public function getStatus(): int
    {
        return (int)$this->_getData(CodeInterface::STATUS);
    }
}
