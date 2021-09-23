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

use Magento\Framework\View\Element\Html\Select;

class Stores extends Select
{
    /**
     * Design
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getOptions() as $groupId => $groupLabel) {
                $this->addOption($groupId, addslashes($groupLabel));
            }
        }

        return parent::_toHtml();
    }

    /**
     * @return array
     */
    protected function _getOptions()
    {
        $options    = [];
        $options[0] = 'All Store Views';
        foreach ($this->storeManager->getStores() as $store) {
            $options[$store->getId()] = $store->getName() . ' — '
                . $store->getBaseUrl() . ' (Id: ' . $store->getId() . ')';
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $objectManager      = \Magento\Framework\App\ObjectManager::getInstance();
        $this->storeManager = $objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);
    }

    /**
     * @param array      $option
     * @param bool|false $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected = false)
    {
        $selectedHtml = $selected ? ' selected="selected"' : '';
        if ($this->getIsRenderToJsTemplate() === true) {
            $selectedHtml .= ' <%= option_extra_attrs.option_' . self::calcOptionHash($option['value']) . ' %>';
        }
        $html = '<option value="' . $this->escapeHtml($option['value']) . '"' . $selectedHtml . '>'
            . $this->escapeHtml($option['label']) .
            '</option>';

        return $html;
    }

    /**
     * @param string $optionValue
     * @return string
     */
    public function calcOptionHash($optionValue)
    {
        return sprintf('%u', crc32($this->getName() . $this->getId() . $optionValue));
    }
}
