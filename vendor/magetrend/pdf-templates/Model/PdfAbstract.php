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

namespace Magetrend\PdfTemplates\Model;

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
abstract class PdfAbstract
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
        \Magetrend\PdfTemplates\Model\Pdf\ElementFactory $elementFactory,
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

    /**
     * Load image from cache
     */
    public function initPdf()
    {
        $this->createCacheDir();
        $this->loadCachedTemplate();
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

        $this->currentPage = end($this->pdf->pages);
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
                    $element['attributes']['top'] = $this->moduleHelper->removePx($element['attributes']['top']);
                    $element['attributes']['top'] += $yOverflow;
                }
            }

            $elementObject = $this->drawElement($element, 1);
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
     * Draw additional elements on each page
     */
    public function drawAdditionalElements()
    {
        $elementsData = $this->getGroupedElementsData();
        if (!isset($elementsData['other'])) {
            return;
        }

        $this->coreRegistry->unregister('pdf_page_count');
        $this->coreRegistry->register('pdf_page_count', count($this->pdf->pages));

        $itemsElement = false;
        foreach ($elementsData['other'] as $key => $element) {
            if (in_array($element['type'], ['element_items', 'element_total', 'element_track'])) {
                continue;
            }

            foreach ($this->pdf->pages as $pageId => $page) {
                $this->currentPage = $page;
                $this->coreRegistry->unregister('pdf_page_current');
                $this->coreRegistry->register('pdf_page_current', $pageId);
                $this->drawElement($element, $pageId);
            }
        }
    }

    /**
     * Returns items element
     *
     * @param $elementsData
     * @return null
     */
    public function getItemsElementData($elementsData)
    {
        if ($this->itemsElement == null) {
            foreach ($elementsData as $element) {
                if ($element['type'] == 'element_items') {
                    $this->itemsElement = $element;
                }
            }
        }
        return $this->itemsElement;
    }

    /**
     * Create new page
     *
     * @return \Zend_Pdf_Page
     */
    public function newPage()
    {
        $page = clone $this->cachedPage;
        $this->pdf->pages[] = $page;
        $this->currentPage = $page;
        return $this->currentPage;
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
            $this->currentPage,
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
     * Create directory for infoice pdf cache
     */
    public function createCacheDir()
    {
        $cacheDir = $this->directoryList->getPath('cache');
        if (!$this->readFactory->create($cacheDir)->isExist(self::PDF_CACHE_DIR)) {
            $path = $this->filesystem->getDirectoryRead(DirectoryList::CACHE)->getAbsolutePath(self::PDF_CACHE_DIR);
            $this->io->mkdir($path, 0775);
        }
    }

    /**
     * Create cache
     *
     * @param $fileName
     * @return string
     */
    public function cacheTemplate($fileName)
    {
        $elementsData = $this->getElementsData();
        $this->pdf = new \Zend_Pdf();
        $page = $this->pdf->newPage($this->getPaperSize());
        $this->currentPage = $page;
        $this->pdf->pages[] = $page;

        if (!empty($elementsData)) {
            foreach ($elementsData as $element) {
                if (isset($element['attributes']['cache']) && $element['attributes']['cache']
                    && ($element['attributes']['cache'] == 1 || $element['attributes']['cache'] == 'true')) {
                    $this->drawElement($element, 1, 1);
                }
            }
        }

        $pathToSave = $this->filesystem->getDirectoryRead(DirectoryList::CACHE)
            ->getAbsolutePath(self::PDF_CACHE_DIR.'/'.$fileName);

        $this->pdf->save($pathToSave);
        $this->currentPage = null;
        return $pathToSave;
    }


    /**
     * Load cache
     */
    public function loadCachedTemplate()
    {
        $fileSuffix = $this->template->getId().'_'.date('Ymdhis', strtotime($this->template->getUpdatedAt()));
        $path = $this->filesystem->getDirectoryRead(DirectoryList::CACHE)->getAbsolutePath(self::PDF_CACHE_DIR);
        $fileName = $this->template->getType().'_template_'.$fileSuffix.'.pdf';
        if (!$this->readFactory->create($path)->isFile($fileName)) {
            $this->cacheTemplate($fileName);
        }

        $this->pdf = new \Zend_Pdf($path.'/'.$fileName, null, true);
        if (!isset($this->pdf->pages[0])) {
            $this->pdf->pages[0]  = $this->pdf->newPage($this->getPaperSize());
        }
        $this->cachedPage = clone $this->pdf->pages[0];
        $this->isCached = true;
        unset($this->pdf->pages[0]);
    }

    /**
     * Returns flag: is cached
     *
     * @return bool
     */
    public function isCached()
    {
        return $this->isCached;
    }

    /**
     * Returns paper size
     * @return string
     */
    public function getPaperSize()
    {
        $size = $this->template->getSize();
        if (empty($size)) {
            return \Zend_Pdf_Page::SIZE_A4;
        }

        return $size;
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
        $tmpDoc = new \Zend_Pdf();
        $tmpPage = $tmpDoc->newPage($this->getPaperSize());
        $tmpDoc->pages[] = $tmpPage;
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
                $tmpPage,
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

    public function getElementByUid($uid)
    {
        $elements = $this->getElementsData();
        foreach ($elements as $element) {
            if ($element['uid'] == $uid) {
                return $element;
            }
        }
        return [];
    }

    public function getEmptyDoc()
    {
        $emptyPdf = new \Zend_Pdf();
        $emptyPdf->pages[0]  = $emptyPdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        return $emptyPdf;
    }

}
