<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-source-pro
 */

namespace Magetrend\PdfTemplates\Model\Adapter\TcPdf;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Draw pdf abstract class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
abstract class TcPdfAbstract
{
    const PDF_CACHE_DIR = 'pdftemplates';

    public $source;

    public $template;

    public $pdf;

    public $element;

    /**
     * @var null
     */
    private $itemsElement = null;

    /**
     * @var null
     */
    public $currentPage = null;

    /**
     * @var null
     */
    public $elemntsData = null;

    /**
     * @var null
     */
    public $groupedElemntsData = null;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    public $io;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    public $readFactory;

    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var null
     */
    private $cachedPage = null;

    /**
     * @var bool
     */
    private $isCached = false;

    /**
     * @var bool
     */
    public $itemsElementData = false;

    /**
     * @var array
     */
    public $elementInfo = [];

    public $elementFactory;

    public $coreRegistry;

    /**
     * Draw element
     * @return mixed
     */
    abstract public function draw();

    /**
     * PdfAbstract constructor.
     *
     * @param File $io
     * @param DirectoryList $directoryList
     * @param Filesystem\Directory\ReadFactory $readFactory
     * @param Filesystem $filesystem
     * @param Element $element
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     */
    public function __construct(
        File $io,
        DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magetrend\PdfTemplates\Model\Element $element,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magetrend\PdfTemplates\Model\Adapter\TcPdf\ElementFactory $elementFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->io = $io;
        $this->directoryList = $directoryList;
        $this->readFactory = $readFactory;
        $this->filesystem = $filesystem;
        $this->element = $element;
        $this->objectManager = $objectManager;
        $this->moduleHelper = $moduleHelper;
        $this->elementFactory = $elementFactory;
        $this->coreRegistry = $registry;
    }

    /**
     * Returns pdf invoice
     * @param $source
     * @param $template
     * @return \Zend_Pdf
     */
    public function getPdf($source, $template)
    {
        $this->coreRegistry->unregister('current_pdf_template');
        $this->coreRegistry->register('current_pdf_template', $template);
        $this->template = $template;
        $this->source = $source;

        $this->elementFactory->resetInstances();
        $this->initPdf();
        $this->draw();

        return $this->pdf;
    }

    public function drawCachedElement()
    {
        $elementsData = $this->getElementsData();
        if (!empty($elementsData)) {
            foreach ($elementsData as $element) {
                if (isset($element['attributes']['cache']) && $element['attributes']['cache']
                    && ($element['attributes']['cache'] == 1 || $element['attributes']['cache'] == 'true')) {
                    $this->drawElement($element, 1, 1);
                }
            }
        }
    }

    /**
     * Draw items
     */
    public function drawItems()
    {
        $elementsData = $this->getGroupedElementsData();
        if (!isset($elementsData['other'])) {
            return;
        }

        $itemsElement = false;
        foreach ($elementsData['other'] as $element) {
            if ($element['type'] == 'element_items') {
                $itemsElement = $element;
            }
        }

        if (!$itemsElement) {
            return;
        }

        $allItemsIsDrawed = false;
        $currentPage = 1;
        $this->itemsElementData = $itemsElement;
        while (!$allItemsIsDrawed) {
            $elementObject = $this->drawElement($itemsElement, $currentPage);
            $allItemsIsDrawed = $elementObject->getIsFinished();
            if (!$allItemsIsDrawed) {
                $this->newPage();
                $currentPage++;
            }
        }
    }

    /**
     * Load image from cache
     */
    public function initPdf()
    {
        $this->pdf = new \TCPDF(PDF_PAGE_ORIENTATION, 'pt', $this->getPaperSize(), true, 'UTF-8', false);
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        $this->pdf->SetMargins(0, 0, 0, false);
        $this->pdf->SetAutoPageBreak(false);
        $this->pdf->SetLineStyle(['width' => 0]);
        $this->pdf->SetLineWidth(0);
        $this->pdf->SetDrawColor(255, 127, 0);
        $this->pdf->setJPEGQuality(100);
        $this->pdf->setTextShadow(['enabled' => false]);
        $this->pdf->setImageScale(1);
    }

    public function getEmptyDoc()
    {
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, 'pt', $this->getPaperSize(), true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        return $pdf;
    }

    public function getPaperSize()
    {
        $size = $this->template->getSize();
        if (empty($size)) {
            $size = '595:842:';
        }

        $size = explode(':', $size);
        return [$size[0], $size[1]];
    }

    /**
     * Create new page
     * @return void
     */
    public function newPage()
    {
        $this->pdf->AddPage();
        $this->drawCachedElement();
    }

    /**
     * Returns elements
     *
     * @return array|null
     */
    public function getElementsData()
    {
        if ($this->elemntsData == null) {
            $this->elemntsData = $this->element->getElementsData($this->template->getId());
        }
        return $this->elemntsData;
    }

    /**
     * Draw element
     *
     * @param $elemetData
     * @param $currentPage
     * @return mixed
     */
    public function drawElement($elemetData, $currentPage)
    {
        $elementModel = $this->elementFactory->getModelByType($elemetData['type']);
        $element =  $elementModel->draw(
            $this->pdf,
            $this->addDefaultValues($elementModel->getConfig(), $elemetData),
            $this->source,
            $this->template,
            $this->getGroupedElementsData(),
            $currentPage
        );

        $orignElement = $this->elemntsData[$element->getId()];
        $this->elementInfo[$element->getUid()] = $element->getInfo($orignElement);
        return $element;
    }

    /**
     * Add default values from config
     *
     * @param $config
     * @param $data
     */
    public function addDefaultValues($config, $data)
    {
        if (isset($config['attributes'])) {
            if (!isset($data['attributes'])) {
                $data['attributes'] = [];
            }
            foreach ($config['attributes'] as $key => $attribute) {
                if (isset($attribute['default']) && !isset($data['attributes'][$key])) {
                    $data['attributes'][$key] = $attribute['default'];
                }
            }
        }
        return $data;
    }

    /**
     * Returns grouped elements
     *
     * @return array|null
     */
    public function getGroupedElementsData()
    {
        if ($this->groupedElemntsData == null) {
            $elementsData = $this->getElementsData();
            $groupedData = [];
            foreach ($this->elemntsData as $element) {
                if (isset($element['attributes']['cache']) && $element['attributes']['cache']
                    && ($element['attributes']['cache'] == 1 || $element['attributes']['cache'] == 'true')) {
                    $groupedData['cache'][] = $element;
                } elseif (isset($element['attributes']['first_page_only'])
                    && $element['attributes']['first_page_only'] == 'true') {
                    $groupedData['first_page'][] = $element;
                } elseif (isset($element['attributes']['last_page_only'])
                    && $element['attributes']['last_page_only']  == 'true') {
                    $groupedData['last_page'][] = $element;
                } else {
                    $groupedData['other'][] = $element;
                }
            }
            $this->groupedElemntsData = $groupedData;
        }
        return $this->groupedElemntsData;
    }

    public function predictSpaceForLastPage()
    {
        $grupedElements = $this->getGroupedElementsData();
        $prediction = 0;
        if (isset($grupedElements['last_page']) && !empty($grupedElements['last_page'])) {
            $prediction = $this->calcElementGroupHeight($grupedElements['last_page']);
        }
        $this->coreRegistry->register('space_prediction_last_page', $prediction, true);
    }

    public function calcElementGroupHeight($elemntsData)
    {
        $tmpDoc = $this->getEmptyDoc();
        $tmpDoc->AddPage();
        $topLine = 99999;
        $bottomLine = 0;
        $height = 0;
        $elementInfoList = [];
        foreach ($elemntsData as $elemetData) {
            if (isset($elemetData['attributes']['depends_on'])
                && !empty($elemetData['attributes']['depends_on'])
                && isset($elementInfoList[$elemetData['attributes']['depends_on']])) {
                $dependsOn = $elementInfoList[$elemetData['attributes']['depends_on']];
                $yOverflow = $dependsOn['pdf_height'] - $dependsOn['design_height'];
                $elemetData['attributes']['top'] = $this->moduleHelper->removePx($elemetData['attributes']['top']);
                $elemetData['attributes']['top'] += $yOverflow;
            }

            $elementModel = $this->elementFactory->getModelByType($elemetData['type']);
            $element =  $elementModel->draw(
                $tmpDoc,
                $this->addDefaultValues($elementModel->getConfig(), $elemetData),
                $this->source,
                $this->template,
                $this->getGroupedElementsData(),
                1
            );

            $orignElement = $this->elemntsData[$element->getId()];
            $elementInfo = $element->getInfo($orignElement);
            $elementInfoList[$element->getUid()] = $elementInfo;

            if (isset($elementInfo['pdf_top']) && isset($elementInfo['pdf_height'])) {
                if ($topLine > $elementInfo['pdf_top']) {
                    $topLine = $elementInfo['pdf_top'];
                }

                if ($bottomLine < $elementInfo['pdf_top'] + $elementInfo['pdf_height']) {
                    $bottomLine = $elementInfo['pdf_top'] + $elementInfo['pdf_height'];
                }
            }
        }

        return ($bottomLine - $topLine);
    }

    /**
     * Draw first page elements
     */
    public function drawFirstPageElements()
    {
        $elementsData = $this->getGroupedElementsData();
        if (!isset($elementsData['first_page'])) {
            return;
        }

        foreach ($elementsData['first_page'] as $element) {
            if (isset($element['attributes']['depends_on'])
                && !empty($element['attributes']['depends_on'])
                && isset($this->elementInfo[$element['attributes']['depends_on']])) {
                $dependsOn = $this->elementInfo[$element['attributes']['depends_on']];
                $yOverflow = $dependsOn['pdf_height'] - $dependsOn['design_height'];
                $element['attributes']['top'] = $this->moduleHelper->removePx($element['attributes']['top']);
                $element['attributes']['top'] += $yOverflow;
            }

            $this->drawElement($element, 1);
        }
    }

    /**
     * Draw last page elements
     */
    public function drawLastPageElements()
    {
        $elementsData = $this->getGroupedElementsData();
        if (!isset($elementsData['last_page'])) {
            return;
        }

        //$this->currentPage = end($this->pdf->pages);
        $itemsEndY = $this->elementFactory->getModelByType('element_items')->getLastItemY();

        $defaultItemsY = 0;
        if ($this->itemsElementData) {
            $top = $this->moduleHelper->removePx($this->itemsElementData['attributes']['top']);
            $height = $this->moduleHelper->removePx($this->itemsElementData['attributes']['table_height']);
            $defaultItemsY = $top + $height;
        }

        foreach ($elementsData['last_page'] as $element) {
            /**
             * Place element after order items
             */
            if ($defaultItemsY > 0
                && isset($element['attributes']['after_order_items'])
                && ($element['attributes']['after_order_items'] == 'true'
                    || $element['attributes']['after_order_items'] == 1)) {
                $element['attributes']['top'] = $this->moduleHelper->removePx($element['attributes']['top'])
                    + ($itemsEndY - $defaultItemsY);
            }

            /**
             * Change top Y if element is depending on previews elements
             */
            if (isset($element['attributes']['depends_on'])
                && !empty($element['attributes']['depends_on'])
                && isset($this->elementInfo[$element['attributes']['depends_on']])
            ) {
                $dependsOn = $this->elementInfo[$element['attributes']['depends_on']];
                $yOverflow = $dependsOn['pdf_height'] - $dependsOn['design_height'];
                if (isset($dependsOn['pdf_bottom_line'])) {
                    $paddingBottom = $this->moduleHelper->removePx($element['attributes']['top'])
                        - ($this->moduleHelper->removePx($dependsOn['design_height'])
                            + $this->moduleHelper->removePx($dependsOn['design_top']));
                    $element['attributes']['top'] = $this->moduleHelper->removePx($dependsOn['pdf_bottom_line'])
                        + $paddingBottom;
                } else {
                    $element['attributes']['top'] += $yOverflow;
                }
            }

            $elementObject = $this->drawElement($element, 1);
        }
    }

    /**
     * Draw additional elements on each page
     */
    public function drawAdditionalElements()
    {

        $elementsData = $this->getGroupedElementsData();
        if (!isset($elementsData['other'])) {
            return;
        }

        $pageCount =  $this->pdf->getNumPages();
        $this->coreRegistry->unregister('pdf_page_count');
        $this->coreRegistry->register('pdf_page_count', $pageCount);

        $itemsElement = false;
        foreach ($elementsData['other'] as $key => $element) {
            if (in_array($element['type'], ['element_items', 'element_total', 'element_track'])) {
                continue;
            }
            for ($i = 1; $i <= $pageCount; $i++) {
                $this->pdf->setPage($i);
                $this->coreRegistry->unregister('pdf_page_current');
                $this->coreRegistry->register('pdf_page_current', $i);
                $this->drawElement($element, $i);
            }
        }
    }
}
