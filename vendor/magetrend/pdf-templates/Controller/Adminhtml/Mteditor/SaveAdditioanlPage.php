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
 * Save info action controller class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class SaveAdditioanlPage extends \Magetrend\PdfTemplates\Controller\Adminhtml\Mteditor
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $request = $this->getRequest();
        $templateId = $this->getRequest()->getParam('id');
        $additionalPage = $request->getParam('additional_page');
        $template = $this->mtEditorManager->initTemplate($templateId);

        if (!$template->getId() && $templateId) {
            return $this->_error(__('This template no longer exists.'));
        } else {
            try {
                $template->setAdditionalPage($additionalPage)
                    ->save();

                return $this->_jsonResponse([
                    'success' => 1,
                ]);
            } catch (\Exception $e) {
                return $this->_error($e->getMessage());
            }
        }
    }
}
