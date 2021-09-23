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

namespace Magetrend\PdfTemplates\Controller\Adminhtml\Mteditor;

/**
 * MtEditor index controller
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Index extends \Magetrend\PdfTemplates\Controller\Adminhtml\Mteditor
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $messages = $this->messageManager->getMessages();
        $this->coreRegistry->register('response_messages_errors', $messages->getErrors());
        $template = $this->mtEditorManager->initTemplate(
            $this->getRequest()->getParam('id')
        );

        if ($template->getId()) {
            if (!$this->_validateConfig($template->getStoreId())) {
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('adminhtml/email_template/index');
            }
        }

        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('MT Editor / Magento Admin'));

        $this->_view->renderLayout();
    }
}
