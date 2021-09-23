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

namespace Magetrend\PdfTemplates\Block\Adminhtml\Template\Edit;

/**
 * Tabs containter block
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    public $coreRegistry;

    /**
     * Tabs constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * Set tab widget attributes
     *
     * @return void
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        parent::_construct();
        $this->setId('template_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Order Comment Template'));
    }

    /**
     * Add tabs to container
     *
     * @return $this
     */
    //@codingStandardsIgnoreLine
    protected function _beforeToHtml()
    {
        $this->addTab(
            'general_section',
            [
                'label' => __('General Settings'),
                'title' => __('General Settings'),
                'active' => true,
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\PdfTemplates\Block\Adminhtml\Template\Edit\Tab\General'
                )->toHtml()
            ]
        );

        $this->addTab(
            'content_section',
            [
                'label' => __('Comment'),
                'title' => __('Comment'),
                'active' => false,
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\PdfTemplates\Block\Adminhtml\Template\Edit\Tab\Content'
                )->toHtml()
            ]
        );
        return parent::_beforeToHtml();
    }
}
