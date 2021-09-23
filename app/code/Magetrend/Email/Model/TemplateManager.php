<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Model;

use Magento\Framework\Exception\LocalizedException;

class TemplateManager
{
    public $templateCollectionFactory;

    public $templateFactory;

    public $emailConfig;

    public $moduleHelper;

    public $transportBuilder;

    public $scopeConfig;

    public function __construct(
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templateCollectionFactory,
        \Magento\Email\Model\TemplateFactory $templateFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magetrend\Email\Helper\Data $moduleHelper,
        \Magento\Framework\Mail\Template\TransportBuilderFactory $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->templateCollectionFactory = $templateCollectionFactory;
        $this->emailConfig = $emailConfig;
        $this->moduleHelper = $moduleHelper;
        $this->templateFactory = $templateFactory;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    public function getTemplateList($designCode = null)
    {
        $mtEmailList = [];
        $templateCollection = $this->templateCollectionFactory->create()
            ->addFieldToFilter('is_mt_email', 1);

        if ($templateCollection->getSize() > 0) {
            foreach ($templateCollection as $template) {
                $mtEmailList[] = [
                    'label' => $template->getTemplateCode(),
                    'value' => $template->getId()
                ];
            }
        }

        $list = $this->emailConfig->getAvailableTemplates();
        foreach ($list as $template) {
            if ($template['group'] != 'Magetrend_Email') {
                continue;
            }
            $mtEmailList[] = $template;
        }

        if ($designCode) {
            foreach ($mtEmailList as $key => $template) {
                if ($designCode == 'load' && isset($template['group'])) {
                    unset($mtEmailList[$key]);
                    continue;
                }

                if ($designCode != 'load' && strpos($template['value'], 'mt_email_'.$designCode.'_') === false) {
                    unset($mtEmailList[$key]);
                    continue;
                }
            }
        }

        return $mtEmailList;
    }

    public function getDesignList()
    {
        $templateList = $this->getTemplateList();
        if (empty($templateList)) {
            return [];
        }

        $designList = [];
        foreach ($templateList as $template) {
            if (!isset($template['group']) || $template['group'] != 'Magetrend_Email') {
                continue;
            }

            $templateDesgin = explode('_', $template['value']);
            if ($templateDesgin[0] != 'mt') {
                continue;
            }

            $designList[$templateDesgin[2]] = 1;
        }

        if (empty($designList)) {
            return [];
        }

        $list = [];
        foreach ($designList as $design => $i) {
            $list[] = [
                'label' => ucfirst($design),
                'value' => $design
            ];
        }

        return $list;
    }

    public function sendTestEmails($templateIds)
    {
        if (empty($templateIds)) {
            return true;
        }

        $to = $this->moduleHelper->getTestEmailAddress();

        if (empty($to)) {
            throw new LocalizedException(__('Email address is empty. Please set email address in configuration.'));
        }

        foreach ($templateIds as $templateId) {
            $this->sendTestEmail($to, $templateId);
        }
        return true;
    }

    public function sendTestEmail($email, $templateId)
    {
        $template = $this->templateFactory->create()
            ->load($templateId);

        $vars = $this->moduleHelper->getDemoVars($template);
        $storeId = $template->getStoreId();

        $transportBuilder = $this->transportBuilder->create()
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ])
            ->setTemplateVars($vars)
            ->setFrom([
                'email' => $this->scopeConfig->getValue(
                    'trans_email/ident_general/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                ),
                'name' =>$this->scopeConfig->getValue(
                    'trans_email/ident_general/name',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                )
            ])
            ->addTo($email);

        $transport = $transportBuilder->getTransport();
        $transport->sendMessage();
    }
}
