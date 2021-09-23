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

namespace Magetrend\PdfTemplates\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magetrend\PdfTemplates\Model\Adapter\ZendPdf;
use Magetrend\PdfTemplates\Model\Config\Source\Adapter;
use Magetrend\PdfTemplates\Model\ResourceModel\Element\Collection;

/**
 * Attachment registry
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class AttachmentRegistry extends \Magento\Framework\Model\AbstractModel
{
    private $parts = [];

    public function addPart($part)
    {
        $this->parts[hash('md5', $part->filename)] = $part;
    }

    public function getParts()
    {
        return $this->parts;
    }

    public function resetParts()
    {
        $this->parts = [];
    }
}
