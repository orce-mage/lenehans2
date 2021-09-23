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

namespace Magetrend\PdfTemplates\Model\Pdf;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Draw invoice pdf
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Shipment extends \Magetrend\PdfTemplates\Model\PdfAbstract
{
    /**
     * Draw invoice
     */
    public function draw()
    {
        $elementsData = $this->getGroupedElementsData();
        if (empty($elementsData)) {
            return;
        }
        $this->newPage();
        $this->drawFirstPageElements();
        $this->predictSpaceForLastPage();
        $this->drawItems();
        $this->drawTracking();
        $this->drawLastPageElements();
        $this->drawAdditionalElements();
    }

    public function drawTracking()
    {
        $elementsData = $this->getGroupedElementsData();
        if (!isset($elementsData['other'])) {
            return;
        }

        $trackElement = false;
        foreach ($elementsData['other'] as $element) {
            if ($element['type'] == 'element_track') {
                $trackElement = $element;
            }
        }

        if (!$trackElement) {
            return;
        }
        $itemsEndY = $this->elementFactory->getModelByType('element_items')->getLastItemY();
        $top = $this->moduleHelper->removePx($this->itemsElementData['attributes']['top']);
        $height = $this->moduleHelper->removePx($this->itemsElementData['attributes']['table_height']);
        $defaultItemsY = $top + $height;
        $trackElement['attributes']['top'] = $this->moduleHelper->removePx($trackElement['attributes']['top'])
            + ($itemsEndY - $defaultItemsY);

        $allItemsIsDrawed = false;
        $currentPage = 1;
        $this->itemsElementData = $trackElement;
        while (!$allItemsIsDrawed) {
            $elementObject = $this->drawElement($trackElement, $currentPage);
            $allItemsIsDrawed = $elementObject->getIsFinished();
            if (!$allItemsIsDrawed) {
                $this->newPage();
                $currentPage++;
            }

            if ($currentPage==10) {
                break;
            }
        }

        $this->itemsElementData = $trackElement;
    }
}
