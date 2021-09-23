<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */


namespace Amasty\Geoip\Block\Adminhtml\Settings;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Amasty\Geoip\Model\Import as ModelImport;

class DownloadNImport extends Field
{
    /**
     * @var ModelImport $import
     */
    protected $import;

    /**
     * DownloadNImport constructor.
     *
     * @param Context $context
     * @param ModelImport $import
     * @param array $data
     */
    public function __construct(
        Context $context,
        ModelImport $import,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->import = $import;
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     *
     * @return string
     *
     * @throws LocalizedException
     */
    public function _getElementHtml(AbstractElement $element)
    {
        $importTypes = [
            'location',
            'block',
            'block_v6'
        ];

        $urls = [];
        foreach ($importTypes as $type) {
            $startDownloadingUrl = $this->getUrl(
                'amasty_geoip/geoip/startDownloading',
                [
                    'type' => $type,
                    'action' => 'download_and_import'
                ]
            );

            $startUrl = $this->getUrl(
                'amasty_geoip/geoip/start',
                [
                    'type' => $type,
                    'action' => 'download_and_import'
                ]
            );

            $processUrl = $this->getUrl(
                'amasty_geoip/geoip/process',
                [
                    'type' => $type,
                    'action' => 'download_and_import'
                ]
            );

            $commitUrl = $this->getUrl(
                'amasty_geoip/geoip/commit',
                [
                    'type' => $type,
                    'action' => 'download_and_import'
                ]
            );

            $urls[] = [
                'start' => $startUrl,
                'process' => $processUrl,
                'commit' => $commitUrl,
                'download' => $startDownloadingUrl
            ];
        }

        $block = $this->getLayout()
            ->createBlock(\Amasty\Geoip\Block\Adminhtml\Template::class)
            ->setTemplate('Amasty_Geoip::download_n_import.phtml')
            ->setConfig(json_encode($urls));
        $this->setDNIData($block);

        return $block->toHtml();
    }

    public function setDNIData($block)
    {
        if ($block->geoipHelper->isDone() && $this->import->importTableHasData()) {
            $width = 100;
            $completedClass = "-completed";
            $importedClass = "-completed";
            $importDate = $block->_scopeConfig->getValue('amgeoip/import/date_download');
            if (!empty($importDate)) {
                $importDate = __('Last Imported: ') . $importDate;
            }
        } else {
            $width = 0;
            $completedClass = "-failed";
            $importedClass = "-failed";
            $importDate = '';
        }
        $block->setWidth($width)
            ->setCompletedClass($completedClass)
            ->setImportedClass($importedClass)
            ->setImportDate($importDate);
    }
}
