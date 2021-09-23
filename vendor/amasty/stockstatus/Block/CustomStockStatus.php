<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Block;

use Amasty\Stockstatus\Model\ConfigProvider;
use Amasty\Stockstatus\Model\Rule;
use Amasty\Stockstatus\Model\Stockstatus\Renderer\Status\DefaultProcessor;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class CustomStockStatus extends Template implements IdentityInterface
{
    const BEFORE_CONTAINER = 'amasty_stockstatus_before';
    const AFTER_CONTAINER = 'amasty_stockstatus_after';
    const TOOLTIP_TEMPLATE = 'Amasty_Stockstatus::tooltip.phtml';

    /**
     * @var string
     */
    protected $_template = 'Amasty_Stockstatus::custom_stock_status.phtml';

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
    }

    public function getTooltipHtml(?string $tooltipText): string
    {
        $html = '';
        if ($tooltipText) {
            $html = $this->_layout->createBlock(Template::class)
                ->setTemplate(self::TOOLTIP_TEMPLATE)
                ->setData('tooltip_text', $tooltipText)
                ->toHtml();
        }

        return $html;
    }

    public function setProduct(ProductInterface $product): void
    {
        $this->product = $product;
    }

    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    public function isIconOnly(): bool
    {
        return $this->configProvider->isIconOnly();
    }

    public function isDefaultStatusDisplayed(): bool
    {
        return $this->configProvider->isShowDefaultStatus() && !$this->isInProductList();
    }

    public function isInProductList(): bool
    {
        return (bool) $this->getData(DefaultProcessor::IN_PRODUCT_LIST);
    }

    public function isWrapperNeed(): bool
    {
        return (bool) $this->getData(DefaultProcessor::ADD_WRAPPER);
    }

    /**
     * @see \Amasty\CustomStockStatusMsi\Plugin\Block\CustomStockStatus\AddOpenPopupStatus
     *
     * @return bool
     */
    public function isSourcePopupOpenedByStatus(): bool
    {
        return false;
    }

    public function getDefaultStatus(): string
    {
        if ($this->getProduct()->isSaleable()) {
            $result = __('In stock');
        } else {
            $result = __('Out of stock');
        }

        return $result->render();
    }

    public function hasStockstatusInformation(): bool
    {
        return $this->getProduct()->getExtensionAttributes()->getStockstatusInformation()->getStatusId() !== null;
    }

    public function isPopupOpened(): bool
    {
        return $this->isWrapperNeed() && $this->isSourcePopupOpenedByStatus();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->hasStockstatusInformation()) {
            $html = parent::_toHtml();
        }

        return $html ?? '';
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        $identities = [];

        if ($ruleId = $this->getProduct()->getExtensionAttributes()->getStockstatusInformation()->getRuleId()) {
            $identities[] = Rule::CACHE_TAG . '_' . $ruleId;
        }

        return $identities;
    }

    public function getAvailability(): string
    {
        return $this->getProduct()->isSaleable() ? 'available' : 'unavailable';
    }
}
