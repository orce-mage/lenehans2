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
 * Draw quote pdf
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Quote extends \Magetrend\PdfTemplates\Model\PdfAbstract
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
        $this->drawLastPageElements();
        $this->drawAdditionalElements();
    }
}