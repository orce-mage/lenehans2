<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class CustomerLink
 */
class CustomerLink extends Column
{
    const URL = 'customer/index/edit';

    const ID_FIELD_NAME = 'customer_id';

    const ID_PARAM_NAME = 'id';

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $url = $this->context->getUrl(
                    self::URL,
                    [self::ID_PARAM_NAME => $item[self::ID_FIELD_NAME]]
                );
                $item[$this->_data['config']['link']] = $url;
            }
        }

        return $dataSource;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $this->_data['config']['link'] = $this->_data['name'] . '_link';
    }
}
