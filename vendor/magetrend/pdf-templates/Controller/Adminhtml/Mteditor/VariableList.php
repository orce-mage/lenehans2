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

use Magento\Framework\App\Filesystem\DirectoryList;
use PHPUnit\Framework\Exception;

/**
 * Preview action controller class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class VariableList extends \Magetrend\PdfTemplates\Controller\Adminhtml\Mteditor
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $templateId = $this->getRequest()->getParam('id');
        $sourceId = $this->getRequest()->getParam('source_id');
        try {
            $list = $this->mtEditorManager->getAvailableVariableList($templateId, $sourceId);
            $blockHtml = $this->_view->getLayout()
                ->createBlock('Magetrend\PdfTemplates\Block\Adminhtml\Mteditor\VariableList')
                ->setData('variable_list', $list)
                ->toHtml();

            return $this->_jsonResponse(['success'=> 1, 'data' => $blockHtml]);
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('<b>Unable to get variable list :( </b> '. $e->getMessage())
            );
            $this->moduleHelper->log($e->getMessage().' '.$e->getTraceAsString());
        }

        $this->_redirect('pdftemplates/mteditor/index', ['id' => $templateId]);
    }
}
