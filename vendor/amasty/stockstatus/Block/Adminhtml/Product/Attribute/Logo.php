<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Block\Adminhtml\Product\Attribute;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Logo extends Template
{
    /**
     * @var array
     */
    private $attributeCodes = [
        'custom_stock_status',
        'custom_stock_status_qty_based',
        'custom_stock_status_qty_rule',
        'quantity_and_stock_status',
        'stock_expected_date'
    ];

    const LOGO_FILE = 'Amasty_Stockstatus::images/amasty_logo.png';

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return array
     */
    public function getAffectedIds()
    {
        $ids = [];

        foreach ($this->attributeCodes as $attributeCode) {
            try {
                $attribute = $this->attributeRepository->get($attributeCode);
                $ids[] = $attribute->getAttributeId();
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        return $ids;
    }

    public function getLogoUrl()
    {
        return $this->getViewFileUrl(self::LOGO_FILE);
    }
}
