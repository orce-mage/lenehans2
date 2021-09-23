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
 * Create action controller class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Create extends \Magetrend\PdfTemplates\Controller\Adminhtml\Mteditor
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        try {
            $template = $this->mtEditorManager
                ->createTemplate($this->getRequest()->getParams());

            return $this->_jsonResponse([
                'success' => 1,
                'redirectTo' => $this->getUrl("*/*/index/", ['id' => $template->getId()])
            ]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->_error($e->getMessage());
        } catch (\Exception $e) {
            return $this->_error($e->getMessage());
        }
    }
}
