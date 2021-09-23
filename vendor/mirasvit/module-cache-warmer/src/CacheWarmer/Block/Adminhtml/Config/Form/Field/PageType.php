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



namespace Mirasvit\CacheWarmer\Block\Adminhtml\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DataObject;
use Mirasvit\CacheWarmer\Model\Config\Source\PageType as PageTypeSource;

/**
 * @method AbstractElement getElement()
 * @method $this setElement(AbstractElement $element)
 */
class PageType extends Field
{
    /**
     * @var PageTypeSource
     */
    private $pageTypeSource;

    /**
     * PageType constructor.
     * @param PageTypeSource $pageTypeSource
     * @param Context $context
     */
    public function __construct(
        PageTypeSource $pageTypeSource,
        Context $context
    ) {
        $this->pageTypeSource = $pageTypeSource;

        return parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);

        return $this->_toHtml();
    }

    /**
     * @return DataObject[]
     */
    public function getPageTypes()
    {
        $result = [];

        $types = $this->pageTypeSource->toOptionArray();
        foreach ($types as $type) {
            $row = new DataObject();
            $row->addData([
                'code'       => $type['value'],
                'label'      => $type['label'],
                'is_active'  => $this->getValue($type['value'], 'is_active'),
                'importance' => $this->getValue($type['value'], 'importance'),
                'order'      => $this->getValue($type['value'], 'order') ? $this->getValue($type['value'], 'order') : 1000,
            ]);

            $result[] = $row;
        }

        usort($result, function ($a, $b) {
            return $a->getData('order') - $b->getData('order');
        });

        return $result;
    }

    /**
     * @param string $key
     * @param string $option
     * @return string
     */
    public function getValue($key, $option)
    {
        if ($this->getElement()->getData('value') && is_array($this->getElement()->getData('value'))) {
            $values = $this->getElement()->getData('value');
            if (isset($values[$key]) && isset($values[$key][$option])) {
                return $values[$key][$option];
            }
        }

        return false;
    }

    /**
     * @param DataObject $type
     * @return string
     */
    public function getNamePrefix($type)
    {
        return $this->getElement()->getName() . '[' . $type->getData('code') . ']';
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->setTemplate('Mirasvit_CacheWarmer::config/form/field/page_type.phtml');
    }
}
