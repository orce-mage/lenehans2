<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Block\Adminhtml\Product\Attribute\Edit\Form\Preview;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Amasty\Stockstatus\Api\StockstatusSettings\GetIconUrlByStockstatusSettingInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\Store;

class Image extends Template
{
    /**
     * @var StockstatusSettingsInterface
     */
    private $settingsModel;

    /**
     * @var GetIconUrlByStockstatusSettingInterface
     */
    private $getIconUrlByStockstatusSetting;

    protected $_template = 'Amasty_Stockstatus::catalog/product/attribute/advanced_settings/image_preview.phtml';

    public function __construct(
        Context $context,
        StockstatusSettingsInterface $settingsModel,
        GetIconUrlByStockstatusSettingInterface $getIconUrlByStockstatusSetting,
        array $data = []
    ) {
        $this->settingsModel = $settingsModel;
        $this->getIconUrlByStockstatusSetting = $getIconUrlByStockstatusSetting;

        parent::__construct(
            $context,
            $data
        );
    }

    public function isImageUseDefault(): bool
    {
        return $this->settingsModel->getOrigData(StockstatusSettingsInterface::STORE_ID) ?
            (bool)$this->settingsModel->getData(StockstatusSettingsInterface::IMAGE_PATH . '_use_default')
            : $this->settingsModel->getStoreId() !== Store::DEFAULT_STORE_ID;
    }

    public function getImageUrl(): ?string
    {
        return $this->getIconUrlByStockstatusSetting->execute($this->settingsModel);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        return $this->getImageUrl() ? parent::_toHtml() : '';
    }
}
