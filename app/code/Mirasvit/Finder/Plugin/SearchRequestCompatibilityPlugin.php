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
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Plugin;

use Magento\Framework\Search\Request\Binder;
use Mirasvit\Core\Service\CompatibilityService;

/**
 * @see \Magento\Framework\Search\Request\Binder::bind()
 */
class SearchRequestCompatibilityPlugin
{
    public function beforeBind(Binder $subject, array $requestData, array $bindData): array
    {
        if (CompatibilityService::is23()) {
            $requestData = $this->fix($requestData);
        }

        return [$requestData, $bindData];
    }

    private function fix(array $data): array
    {
        $fieldName       = 'entity_id';
        $fieldFilterName = $fieldName . '_filter';

        if (!isset($data['query']) ||
            ($data['query'] != 'catalog_view_container' && $data['query'] != 'quick_search_container')
        ) {
            return $data;
        }

        if (!isset($data['queries']['catalog_view_container'])) {
            return $data;
        }

        foreach ($data['queries']['catalog_view_container']['queryReference'] as $k => $row) {
            if ($row['ref'] == 'id') {
                $data['queries']['catalog_view_container']['queryReference'][$k]['ref'] = $fieldName;
            }
        }

        if (isset($data['queries']['id'])) {
            $data['queries'][$fieldName] = $data['queries']['id'];

            $data['queries'][$fieldName]['name']                      = $fieldName;
            $data['queries'][$fieldName]['filterReference'][0]['ref'] = $fieldFilterName;

            unset($data['queries']['id']);
        }

        if (isset($data['filters']['id_filter'])) {
            $data['filters'][$fieldFilterName] = $data['filters']['id_filter'];

            $data['filters'][$fieldFilterName]['name']  = $fieldFilterName;
            $data['filters'][$fieldFilterName]['field'] = $fieldName;
            $data['filters'][$fieldFilterName]['value'] = '$' . $fieldName . '$';

            unset($data['filters']['id_filter']);
        }

        return $data;
    }
}
