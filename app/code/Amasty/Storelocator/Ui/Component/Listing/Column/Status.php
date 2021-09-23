<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Amasty\Storelocator\Model\Config\Source\ReviewStatuses;

/**
 * Class Status
 */
class Status extends Column
{
    /**
     * @var StatusFilter
     */
    private $reviewStatuses;

    public function __construct(
        ReviewStatuses $reviewStatuses,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->reviewStatuses = $reviewStatuses;
    }

    public function prepare()
    {
        $data = $this->getData();
        $data['config']['editor']['options'] = $this->reviewStatuses->toOptionArray();
        $this->setData($data);
        parent::prepare();
    }
}
