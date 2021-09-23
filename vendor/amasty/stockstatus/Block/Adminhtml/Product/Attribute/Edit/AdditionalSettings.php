<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Block\Adminhtml\Product\Attribute\Edit;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Amasty\Stockstatus\Controller\Adminhtml\Product\Attribute\Settings\Save;
use Amasty\Stockstatus\Model\Backend\StockstatusSettings\Form\ParamsProvider;
use Amasty\Stockstatus\Model\Source\StockStatus;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Serialize\Serializer\Json as Serializer;

class AdditionalSettings extends Template
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ParamsProvider
     */
    private $paramsProvider;

    public function __construct(
        Context $context,
        Serializer $serializer,
        ParamsProvider  $paramsProvider,
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->paramsProvider = $paramsProvider;

        parent::__construct(
            $context,
            $data
        );
    }

    public function isCanAddStockStatusSettings(): bool
    {
        return $this->_authorization->isAllowed(Save::ADMIN_RESOURCE)
            && $this->paramsProvider->getCurrentAttributeCode() === StockStatus::ATTIRUBTE_CODE;
    }

    /**
     * @return string
     */

    public function getJsConfig(): string
    {
        return $this->serializer->serialize([
            'url' => $this->getUrl(
                'amstockstatus/product_attribute/settings',
                [StockstatusSettingsInterface::OPTION_ID => '__optionId__']
            )
        ]);
    }
}
