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
class Preview extends \Magetrend\PdfTemplates\Controller\Adminhtml\Mteditor
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
        $sourceId = $this->getRequest()->getParam('invoice_id');
        try {
            $template = $this->mtEditorManager->initTemplate($templateId);
            $sourceObject = $this->typeManager
                ->setTemplateType($template->getType())
                ->getAdapter()
                ->getObjectById($sourceId);

            if (!$sourceObject) {
                throw new \Exception(__('Ops'));
            }

            $fileName = sprintf('preview_%s.pdf', time());
            $path = $template->createPdf([$sourceObject], DirectoryList::TMP, $fileName, $templateId);

            $file = explode('/', $path);
            return $this->fileFactory->create(
                $fileName,
                [
                    'value'=> $fileName,
                    'type' => 'filename',
                    'rm' => true
                ],
                DirectoryList::TMP
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(
                __('<b>Unable to generate PDF :( </b> '. $e->getMessage())
            );
            $this->moduleHelper->log($e->getMessage().' '.$e->getTraceAsString());
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('<b>Unable to generate PDF :( </b> '. $e->getMessage())
            );
            $this->moduleHelper->log($e->getMessage().' '.$e->getTraceAsString());
        }

        $this->_redirect('pdftemplates/mteditor/index', ['id' => $templateId]);
    }
}
