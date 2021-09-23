<?php
/**
 * Copyright © 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageBig\Shopbybrand\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class IsFeatured extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'thumbnail';
    const ALT_FIELD = 'name';
	const BRAND_URL_PATH_EDIT = 'shopbybrand/index/edit';
	const BRAND_URL_PATH_DELETE = 'shopbybrand/index/delete';
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        
        parent::__construct($context, $uiComponentFactory, $components, $data);
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->isFeaturedByDefault = 0;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {	
   	    if (isset($dataSource['data']['items'])) {
			$objectManager = $this->_objectManager;
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['brand_object'])) {
                    $brand = $item['brand_object'];
                } else {
                    $model = $objectManager->create('MageBig\Shopbybrand\Model\Brand');
                    $model->setOptionId($item['option_id']);
                    $brand = $model->load(null);
                    $item['brand_object'] = $brand;
                }
                $data = $brand->getData();
                
                if (isset($data['mb_brand_is_featured'])) {
                    if ($data['mb_brand_is_featured'] == 1) {
                        $item[$fieldName] = __('Yes');
                    } else {
                        $item[$fieldName] = __('No');
                    }
                } else {
                    if ($this->isFeaturedByDefault) {
                        $item[$fieldName] = __('Yes');
                    } else {
                        $item[$fieldName] = __('No');
                    }
                }
            }
        }
        return $dataSource;
    }
}
