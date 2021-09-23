<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Backend\StockstatusSettings\Form;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;

class ParamsProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        RequestInterface $request,
        Registry $registry
    ) {
        $this->request = $request;
        $this->registry = $registry;
    }

    public function getStoreId(): int
    {
        return (int)$this->request->getParam(Store::ENTITY, Store::DEFAULT_STORE_ID);
    }

    public function getOptionId(): int
    {
        return (int)$this->request->getParam(StockstatusSettingsInterface::OPTION_ID);
    }

    public function getCurrentAttributeCode(): string
    {
        /** @var $attribute Attribute */
        $attribute = $this->registry->registry('entity_attribute');

        return $attribute && $attribute->getAttributeCode() ? $attribute->getAttributeCode() : '';
    }
}
