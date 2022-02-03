<?php

namespace Amasty\Faq\Block\RichData;

use Amasty\Faq\Block\AbstractBlock;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\DataCollector;
use Magento\Framework\View\Element\Template\Context;

class JsonLd extends AbstractBlock
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var DataCollector
     */
    private $dataCollector;

    public function __construct(
        Context $context,
        ConfigProvider $configProvider,
        DataCollector $dataCollector,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->dataCollector = $dataCollector;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $result = '';
        $data = $this->prepareRichData();
        if (!empty($data)) {
            foreach ($data as $section) {
                $encodedData = \Zend_Json::encode($section);
                $result .= '<script type="application/ld+json">' . $encodedData . '</script>';
            }
        }

        return $result;
    }

    /**
     * Prepare rich snippets data
     *
     * @return array
     */
    public function prepareRichData()
    {
        $data = [];
        $this->addOrganizationData($data);
        $this->addBreadcrumbsData($data);

        return $data;
    }

    /**
     * Add organization data
     *
     * @param array $data
     */
    protected function addOrganizationData(&$data)
    {
        if (!$this->configProvider->isAddRichDataOrganization()) {
            return;
        }

        $data['organization'] = [
            '@context' => 'http://schema.org',
            '@type' => 'Organization',
            'url' => $this->configProvider->getRichDataWebsiteUrl(),
            'logo' => $this->configProvider->getRichDataLogoUrl()
        ];

        if ($name = $this->configProvider->getRichDataOrganizationName()) {
            $data['organization']['name'] = $name;
        }

        if ($this->configProvider->isAddRichDataContact()) {
            $contact = [
                '@type' => 'ContactPoint',
                'contactType' => $this->configProvider->getRichDataContactType(),
                'telephone' => $this->configProvider->getRichDataTelephone()
            ];
            $data['organization']['contactPoint'] = $contact;
        }
    }

    /**
     * Add breadcrumbs data
     *
     * @param array $data
     */
    protected function addBreadcrumbsData(&$data)
    {
        if (!$this->configProvider->isAddRichDataBreadcrumbs()) {
            return;
        }

        $breadcrumbs = $this->dataCollector->getData('breadcrumbs');
        if (is_array($breadcrumbs)) {
            $items = [];
            $position = 0;
            $breadcrumbListName = '';
            foreach ($breadcrumbs as $breadcrumb) {
                if (!array_key_exists('link', $breadcrumb)
                    || !$breadcrumb['link']
                    || !array_key_exists('label', $breadcrumb)
                    || !$breadcrumb['label']
                ) {
                    continue;
                }
                $items[]= [
                    '@type' => 'ListItem',
                    'position' => ++$position,
                    'name' => $breadcrumb['label'],
                    'item' => $breadcrumb['link']
                ];
                $breadcrumbListName = $breadcrumb['label'];
            }

            if (isset($breadcrumb) && array_key_exists('label', $breadcrumb)) {
                $items[]= [
                    '@type' => 'ListItem',
                    'position' => ++$position,
                    'name' => $breadcrumb['label']
                ];
                $breadcrumbListName = $breadcrumb['label'];
            }

            if (!empty($items)) {
                $data['breadcrumbs'] = [
                    '@context' => 'http://schema.org',
                    '@type' => 'BreadcrumbList',
                    'name' => $breadcrumbListName,
                    'numberOfItems' => count($items),
                    'itemListElement' => $items
                ];
            }
        }
    }
}
