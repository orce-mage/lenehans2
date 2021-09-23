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
use Magento\Framework\Exception\NotFoundException;

/**
 * Mime Attachments manager class
 */
class MimeAttachmentManager
{

    public $registry;

    public $eventManager;

    public $moduleHelper;

    private $additionalParts = null;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Event\Manager $eventManager,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper
    ) {
        $this->registry = $registry;
        $this->eventManager = $eventManager;
        $this->moduleHelper = $moduleHelper;
    }

    public function addPart($part)
    {
        $this->additionalParts[] = $part;
    }

    public function getParts()
    {
        return $this->additionalParts;
    }

    public function resetParts()
    {
        $this->additionalParts = null;
    }

    public function collectParts()
    {
        if (!$this->moduleHelper->isActive()) {
            return;
        }

        if ($this->additionalParts === null) {
            $this->additionalParts = [];
            $this->eventManager->dispatch('mt_pdf_collect_email_parts', [
                'template_id' => $this->registry->registry(AttachmentManager::REGISTRY_KEY_TEMPLATE_ID),
                'template_vars' => $this->registry->registry(AttachmentManager::REGISTRY_KEY_TEMPLATE_VARS),
            ]);
        }
    }
}
