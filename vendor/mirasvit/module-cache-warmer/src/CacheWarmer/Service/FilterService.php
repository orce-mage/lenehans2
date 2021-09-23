<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Service;

use Magento\Catalog\Model\Layer\Category\FilterableAttributeList;
use Mirasvit\CacheWarmer\Api\Service\FilterServiceInterface;

/**
 * @todo Remove
 */
class FilterService implements FilterServiceInterface
{
    /**
     * @var FilterableAttributeList
     */
    private $filterableAttributeList;

    /**
     * FilterService constructor.
     * @param FilterableAttributeList $filterableAttributeList
     */
    public function __construct(
        FilterableAttributeList $filterableAttributeList
    ) {
        $this->filterableAttributeList = $filterableAttributeList;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeoFilterPage($pageType, $params)
    {
        if ($pageType = 'catalog_category_view') {
            $appliedAttribute = false;
            $attributes       = $this->filterableAttributeList->getList();

            $attributeCode = [];
            if (is_object($attributes)
                && ($attributesArray = $attributes->toArray())
                && isset($attributesArray['items'])) {
                foreach ($attributesArray['items'] as $attribute) {
                    $attributeCode[$attribute['attribute_code']] = $attribute['attribute_code'];
                }
            }

            if ($attributeCode) {
                $appliedAttribute = array_intersect_key($params, $attributeCode);
            }

            if ($appliedAttribute) {
                return true;
            }
        }

        return false;
    }
}
