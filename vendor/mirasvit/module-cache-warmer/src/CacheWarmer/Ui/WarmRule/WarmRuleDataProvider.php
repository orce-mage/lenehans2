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



namespace Mirasvit\CacheWarmer\Ui\WarmRule;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Mirasvit\CacheWarmer\Api\Data\WarmRuleInterface;

class WarmRuleDataProvider extends DataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $result = [];

        $result['items'] = [];

        /** @var WarmRuleInterface $warmRule */
        foreach ($searchResult->getItems() as $warmRule) {
            $itemData = [
                'id_field_name'             => WarmRuleInterface::ID,
                WarmRuleInterface::ID        => $warmRule->getId(),
                WarmRuleInterface::NAME      => $warmRule->getName(),
                WarmRuleInterface::IS_ACTIVE => $warmRule->isActive(),
                WarmRuleInterface::PRIORITY  => $warmRule->getPriority(),
            ];

            $headers = [];
            foreach ($warmRule->getHeaders() as $header => $value) {
                $headers[] = "$header: $value";
            }
            $itemData['headers'] = implode(PHP_EOL, $headers);

            $varyData = [];
            foreach ($warmRule->getVaryData() as $header => $value) {
                $varyData[] = "$header: $value";
            }
            $itemData['vary_data'] = implode(PHP_EOL, $varyData);

            $result[$warmRule->getId()] = $itemData;
            $result['items'][]         = $itemData;
        }

        $result['totalRecords'] = $searchResult->getTotalCount();

        return $result;
    }
}
