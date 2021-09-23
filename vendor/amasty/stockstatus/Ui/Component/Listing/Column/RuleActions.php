<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class RuleActions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $item[$name]['edit'] = [
                    'href'  => $this->urlBuilder->getUrl(
                        'amstockstatus/rule/edit',
                        ['id' => $item['id']]
                    ),
                    'label' => __('Edit')
                ];
                $item[$name]['duplicate'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amstockstatus/rule/duplicate',
                        ['id' => $item['id']]
                    ),
                    'label' => __('Duplicate'),
                    'post' => true
                ];
                $item[$name]['delete'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amstockstatus/rule/delete',
                        ['id' => $item['id']]
                    ),
                    'label'   => __('Delete'),
                    'confirm' => [
                        'title'   => __('Delete ${ $.$data.name }'),
                        'message' => __('Are you sure you want to delete a ${ $.$data.name } rule?'),
                        '__disableTmpl' => false
                    ],
                    'post' => true
                ];
            }
        }

        return $dataSource;
    }
}
