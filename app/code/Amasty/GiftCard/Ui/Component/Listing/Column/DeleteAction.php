<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Ui\Component\Listing\Column;

use Amasty\GiftCard\Api\Data\CodeInterface;
use Amasty\GiftCard\Model\OptionSource\Status;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class DeleteAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

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

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$this->getData('config/indexField')])
                    && isset($item[CodeInterface::STATUS])
                    && $item[CodeInterface::STATUS] == Status::AVAILABLE
                ) {
                    $config = (array) $this->getData('config');
                    if ($config && isset($config['buttons'])) {
                        foreach ($config['buttons'] as $actionName => $button) {
                            $item[$this->getData('name')][$actionName] = [
                                'label' => $button['itemLabel'],
                                'callback' => $button['callback']
                            ];
                        }
                    }
                }
            }
        }

        return $dataSource;
    }
}
