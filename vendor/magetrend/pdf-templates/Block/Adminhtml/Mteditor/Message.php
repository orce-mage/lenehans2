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

namespace Magetrend\PdfTemplates\Block\Adminhtml\Mteditor;

/**
 * Message block class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Message extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;


    /**
     * Message constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Message\ManagerInterface $manager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->messageManager = $manager;
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Returns error messages
     *
     * @return array
     */
    public function getErrorMessages()
    {
        $messages = $this->coreRegistry->registry('response_messages_errors');
        if (empty($messages)) {
            return false;
        }

        return $messages;
    }
}
