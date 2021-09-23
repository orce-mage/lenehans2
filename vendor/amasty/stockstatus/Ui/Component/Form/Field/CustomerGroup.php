<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Ui\Component\Form\Field;

use Amasty\Stockstatus\Model\Source\CustomerGroup as CustomerGroupSource;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Form\Field;

class CustomerGroup extends Field
{
    /**
     * @var CustomerGroupSource
     */
    private $customerGroupSource;

    public function __construct(
        CustomerGroupSource $customerGroupSource,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->customerGroupSource = $customerGroupSource;
    }

    public function prepare()
    {
        $this->_data['config']['default'] = $this->getDefaultValue();

        parent::prepare();
    }

    private function getDefaultValue(): string
    {
        $customerGroupIds = array_keys($this->customerGroupSource->toArray());
        return implode(',', $customerGroupIds);
    }
}
