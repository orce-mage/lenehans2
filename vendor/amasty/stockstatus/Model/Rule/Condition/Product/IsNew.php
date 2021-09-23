<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Rule\Condition\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogRule\Model\Rule\Condition\Product as ProductCondition;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class IsNew extends ProductCondition
{
    const ATTRIBUTE_CODE = 'am_stockstatus_is_new';

    /**
     * Validation processed with join in collectValidatedAttributes
     *
     * @param AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        return true;
    }

    /**
     * @param ProductCollection $productCollection
     * @return IsNew
     */
    public function collectValidatedAttributes($productCollection)
    {
        /** @var TimezoneInterface $localeDate */
        if ($localeDate = $this->getData('localeDate')) {
            $this->joinAttr($productCollection, 'news_from_date');
            $this->joinAttr($productCollection, 'news_to_date');

            $currentDate = $localeDate->date()->format($this->getNewDateFormat());

            if ($this->getValue()) {
                $productCollection->addFieldToFilter('news_from_date', ['lteq' => $currentDate]);
                $productCollection->addFieldToFilter('news_to_date', ['gteq' => $currentDate]);
            } else {
                $productCollection->addFieldToFilter([
                    ['attribute' => 'news_to_date', ['lt' => $currentDate]],
                    ['attribute' => 'news_from_date', ['gt' => $currentDate]]
                ]);
            }
        }

        return $this;
    }

    /**
     * If Staging module enabled, date format must be Y-m-d H:i:s, because schedule cofigured with minutes.
     * Else date format must be Y-m-d, because date saved without minutes.
     *
     * @return string
     */
    private function getNewDateFormat(): string
    {
        /** @var ModuleManager $moduleManager */
        $moduleManager = $this->getData('moduleManager');

        return $moduleManager->isEnabled('Magento_CatalogStaging')
            ? DateTime::DATETIME_PHP_FORMAT
            : DateTime::DATE_PHP_FORMAT;
    }

    private function joinAttr(ProductCollection $productCollection, string $attributeCode): void
    {
        $productCollection->joinAttribute(
            $attributeCode,
            sprintf('catalog_product/%s', $attributeCode),
            'entity_id',
            null,
            'left',
            $productCollection->getStoreId()
        );
    }

    /**
     * Render element HTML
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . __(
                'Is New %1 %2',
                $this->getOperatorElementHtml(),
                $this->getValueElement()->getHtml()
            )
            . $this->getRemoveLinkHtml();
    }

    /**
     * @return array
     */
    public function getValueSelectOptions()
    {
        return [
            ['value' => 0, 'label' => __('No')],
            ['value' => 1, 'label' => __('Yes')]
        ];
    }

    /**
     * Value element type getter
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * Specify allowed comparison operators
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        parent::loadOperatorOptions();
        $this->setOperatorOption(['==' => __('is')]);

        return $this;
    }

    /**
     * @return string
     */
    public function getAttribute()
    {
        return self::ATTRIBUTE_CODE;
    }
}
