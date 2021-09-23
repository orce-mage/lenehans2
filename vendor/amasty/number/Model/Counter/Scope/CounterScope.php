<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model\Counter\Scope;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CounterScope
{
    const SCOPE_DEFAULT_VALUE = 0;
    const SCOPE_DEFAULT = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
    const SCOPE_WEBSITE = ScopeInterface::SCOPE_WEBSITE;
    const SCOPE_STORE = ScopeInterface::SCOPE_STORE;

    /**
     * @var mixed|string
     */
    private $scopeTypeId = self::SCOPE_DEFAULT;

    /**
     * @var int
     */
    private $scopeId = self::SCOPE_DEFAULT_VALUE;

    /**
     * @return string|null
     */
    public function getScopeTypeId()
    {
        return $this->scopeTypeId;
    }

    /**
     * @param string $typeId
     * @return $this
     */
    public function setScopeTypeId(string $typeId)
    {
        $this->scopeTypeId = $typeId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getScopeId(): int
    {
        return $this->scopeId;
    }

    /**
     * @param $scopeId
     * @return $this
     */
    public function setScopeId($scopeId)
    {
        $this->scopeId = (int)$scopeId;
        return $this;
    }
}
