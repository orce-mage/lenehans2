<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */

namespace Magetrend\PdfTemplates\Model\Adapter;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magetrend\PdfTemplates\Model\AdapterAbstract;

/**
 * TCPDF Adapter
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class TcPdf extends AdapterAbstract
{
    public $template;

    /**
     * @var \Magento\Framework\Filesystem
     */
    public $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|null
     */
    public $directory = null;

    /**
     * @var \Magento\Framework\Simplexml\Config|null
     */
    public $simpleXml = null;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    public $readFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    public $dataCollectionFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    public $dataObjectFactory;

    /**
     * @var TcPdf\Order
     */
    public $orderPdf;

    /**
     * @var ResourceModel\Element\CollectionFactory
     */
    public $elementCollectionFactory;

    /**
     * @var ResourceModel\Attribute\CollectionFactory
     */
    public $attributeCollectionFactory;

    /**
     * @var array
     */
    public $pdfDocs = [];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    public $emulation;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    public $repository;

    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var ResourceModel\Template
     */
    public $templateResource;

    /**
     * @var TemplateFactory
     */
    public $templateFactory;

    public $registry;

    public $file;

    public $typeManager;

    /**
     * Template constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\CollectionFactory $dataCollectionFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Simplexml\Config $simpleXml
     * @param Pdf\Invoice $invoicePdf
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ResourceModel\Element\CollectionFactory $elementCollectionFactory
     * @param ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\App\Emulation $emulation
     * @param \Magento\Framework\View\Asset\Repository $repository
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\CollectionFactory $dataCollectionFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Simplexml\Config $simpleXml,
        \Magetrend\PdfTemplates\Model\Adapter\TcPdf\Order $orderPdf,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magetrend\PdfTemplates\Model\ResourceModel\Element\CollectionFactory $elementCollectionFactory,
        \Magetrend\PdfTemplates\Model\ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Framework\View\Asset\Repository $repository,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magetrend\PdfTemplates\Model\ResourceModel\Template $templateResource,
        \Magetrend\PdfTemplates\Model\TemplateFactory $templateFactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magetrend\PdfTemplates\Model\TypeManager $typeManager
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->filesystem = $filesystem;
        $this->readFactory = $readFactory;
        $this->simpleXml = $simpleXml;
        $this->directory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $this->objectManager = $objectManager;
        $this->orderPdf = $orderPdf;
        $this->storeManager = $storeManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->dataCollectionFactory = $dataCollectionFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->elementCollectionFactory = $elementCollectionFactory;
        $this->emulation = $emulation;
        $this->repository = $repository;
        $this->moduleHelper = $moduleHelper;
        $this->templateResource = $templateResource;
        $this->templateFactory = $templateFactory;
        $this->registry = $registry;
        $this->file = $file;
        $this->typeManager = $typeManager;
    }

    public function createPdf($objects, $dir, $fileName, $templateId = null)
    {
        $path = $this->filesystem->getDirectoryRead($dir)->getAbsolutePath($fileName);
        $docs = $this->getPdf($objects, $templateId);
        return $docs->save($path);
    }

    /**
     * @param array $objects
     * @param null $forceTemplateId
     * @return \Zend_Pdf
     */
    public function getPdf($objects = [], $forceTemplateId = null)
    {
        $tmpDirectory = $this->filesystem->getDirectoryRead(DirectoryList::TMP);
        if (empty($objects)) {
            $pdf = $this->orderPdf->getEmptyDoc();
            return [$this->exportToTmpFile($pdf)];
        }

        $docs = [];
        $this->resetTemplate();
        $currentStore = $this->storeManager->getStore()->getId();
        foreach ($objects as $object) {
            $this->emulation->startEnvironmentEmulation(
                $object->getStoreId(),
                \Magento\Framework\App\Area::AREA_FRONTEND,
                true
            );

            $typeAdapter = $this->typeManager->getAdapter($object);
            $templateId = $this->moduleHelper->getPdfTemplateId($typeAdapter->getType(), $object->getStoreId(), $this->getOrderId($object));

            if ($this->registry->registry('mt_pdf_force_template_id')) {
                $templateId = $this->registry->registry('mt_pdf_force_template_id');
            }

            if ($forceTemplateId !== null) {
                $templateId = $forceTemplateId;
            }

            if ($templateId > 0) {
                $this->getTemplate()->load($templateId);
                $pdfProcessor = $typeAdapter->getTcPdfProcessor();
                $docs[] = $this->exportToTmpFile($pdfProcessor->getPdf($object, $this->getTemplate()));
            } else {
                $pdf = $this->getTemplate()->getDefaultPdf($object);
                if ($pdf) {
                    $docs[] = $this->exportToTmpFile($pdf);
                }
            }

            $this->emulation->stopEnvironmentEmulation();
            $docs = array_merge($docs, $this->getAdditionalPage([$object]));
        }

        $pdf = $this->mergeWithZend($docs);
        $this->cleanTmp($docs);
        return $pdf;
    }

    public function resetTemplate()
    {
        $this->pdfDocs = [];
        $this->typeManager->resetManager();
    }

    public function getAdditionalPage($objects = [])
    {
        $additionalPage = $this->getTemplate()->getAdditionalPage();
        if (empty($additionalPage)) {
            return [];
        }

        $additionalPdf = [];
        foreach ($additionalPage as $page) {
            if ($page['template_id'] == $this->getTemplate()->getId()) {
                continue;
            }

            $template = $this->templateFactory->create();
            $additionalPdf[] = $this->exportToTmpFile($template->getPdf($objects, $page['template_id']));
        }

        return $additionalPdf;
    }

    public function mergeWithZend($pdfDocs)
    {
        $finalDoc =  new \Zend_Pdf();
        $docsCount = count($pdfDocs);
        foreach ($pdfDocs as $i => $path) {
            $tmp = \Zend_Pdf::load($path);
            $pagesCount = count($tmp->pages);
            foreach ($tmp->pages as $page) {
                $finalDoc->pages[] = new \Zend_Pdf_Page(clone $page);
            }

            if ($this->moduleHelper->isDuplexModeEnabled()) {
                if ($docsCount > 1 && $pagesCount % 2 == 1 && $i != $docsCount - 1) {
                    $finalDoc->pages[] = new \Zend_Pdf_Page(\Zend_Pdf_Page::SIZE_A4);
                }
            }
        }

        return $finalDoc;
    }

    public function exportToTmpFile($pdf)
    {
        $tmpDirectory = $this->filesystem->getDirectoryRead(DirectoryList::TMP);
        $pdfPath = $tmpDirectory->getAbsolutePath((time().'-'.rand(0, 99999).'-'.rand(0, 99999)).'-tmp.pdf');
        if ($pdf instanceof \TCPDF) {
            $pdf->Output($pdfPath, 'F');
        } else {
            $pdf->save($pdfPath);
        }
        return $pdfPath;
    }

    public function cleanTmp($docs)
    {
        foreach ($docs as $path) {
            try {
                $this->file->deleteFile($path);
            } catch (FileSystemException $e) {
                continue;
            }
        }
    }
}
