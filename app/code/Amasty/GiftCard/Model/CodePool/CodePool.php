<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\CodePool;

use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Amasty\GiftCard\Api\Data\CodePoolRuleInterface;
use Magento\Framework\Model\AbstractModel;

class CodePool extends AbstractModel implements CodePoolInterface
{
    const DATA_PERSISTOR_KEY = 'amgcard_codepool';

    public function _construct()
    {
        $this->_init(ResourceModel\CodePool::class);
        $this->setIdFieldName(CodePoolInterface::CODE_POOL_ID);
    }

    public function setCodePoolId(int $id): CodePoolInterface
    {
        return $this->setData(CodePoolInterface::CODE_POOL_ID, $id);
    }

    public function getCodePoolId(): int
    {
        return (int)$this->_getData(CodePoolInterface::CODE_POOL_ID);
    }

    public function setTitle(string $title): CodePoolInterface
    {
        return $this->setData(CodePoolInterface::TITLE, $title);
    }

    public function getTitle(): string
    {
        return $this->_getData(CodePoolInterface::TITLE);
    }

    public function setTemplate(string $template): CodePoolInterface
    {
        return $this->setData(CodePoolInterface::TEMPLATE, $template);
    }

    public function getTemplate(): string
    {
        return $this->_getData(CodePoolInterface::TEMPLATE);
    }

    public function setCodePoolRule(CodePoolRuleInterface $rule): CodePoolInterface
    {
        return $this->setData(CodePoolInterface::CODE_POOL_RULE, $rule);
    }

    public function getCodePoolRule()
    {
        return $this->_getData(CodePoolInterface::CODE_POOL_RULE);
    }
}
