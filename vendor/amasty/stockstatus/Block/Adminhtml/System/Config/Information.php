<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Information extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var string
     */
    private $userGuide = 'https://amasty.com/docs/doku.php?id=magento_2:custom_stock_status';

    /**
     * @var array
     */
    private $enemyExtensions = [];

    /**
     * @var string
     */
    private $content;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->moduleManager = $moduleManager;
    }

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $this->setContent(__('Please update Amasty Base module. Re-upload it and replace all the files.'));

        $this->_eventManager->dispatch(
            'amasty_base_add_information_content',
            ['block' => $this]
        );

        $html .= $this->getContent();
        $html .= $this->_getFooterHtml($element);

        $html = str_replace(
            'amasty_information]" type="hidden" value="0"',
            'amasty_information]" type="hidden" value="1"',
            $html
        );
        $html = preg_replace('(onclick=\"Fieldset.toggleCollapse.*?\")', '', $html);

        return $html;
    }

    /**
     * @return array|string
     */
    public function getAdditionalModuleContent()
    {
        if ($this->moduleManager->isEnabled('Magento_GraphQl')
            && !$this->moduleManager->isEnabled('Amasty_StockstatusGraphQl')
        ) {
            $result[] = [
                'type' => 'message-notice',
                'text' => __('Enable stockstatus-graphql module to '
                    . 'activate GraphQl and Custom Stock Status compatibility. '
                    . 'Please, run the following command in the SSH: '
                    . 'composer require amasty/stockstatus-graphql')
            ];
        }

        if ($this->moduleManager->isEnabled('Magento_InventoryCatalog')
            && !$this->moduleManager->isEnabled('Amasty_CustomStockStatusMsi')
        ) {
            $result[] = [
                'type' => 'message-notice',
                'text' => __('Enable custom-stock-status-msi module to '
                    . 'activate Magento MSI and Custom Stock Status compatibility. '
                    . 'Please, run the following command in the SSH: '
                    . 'composer require amasty/custom-stock-status-msi')
            ];
        }

        return $result ?? '';
    }

    /**
     * @return string
     */
    public function getUserGuide()
    {
        return $this->userGuide;
    }

    /**
     * @param string $userGuide
     */
    public function setUserGuide($userGuide)
    {
        $this->userGuide = $userGuide;
    }

    /**
     * @return array
     */
    public function getEnemyExtensions()
    {
        return $this->enemyExtensions;
    }

    /**
     * @param array $enemyExtensions
     */
    public function setEnemyExtensions($enemyExtensions)
    {
        $this->enemyExtensions = $enemyExtensions;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}
