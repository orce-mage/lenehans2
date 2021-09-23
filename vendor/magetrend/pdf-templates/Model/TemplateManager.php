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

/**
 * Template manager class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class TemplateManager
{
    /**
     * @var TemplateFactory
     */
    public $templateFactory;

    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * TemplateManager constructor.
     *
     * @param \Magetrend\PdfTemplates\Model\TemplateFactory $templateFactory
     * @param \Magetrend\PdfTemplates\Helper\Data $dataHelper
     */
    public function __construct(
        \Magetrend\PdfTemplates\Model\TemplateFactory $templateFactory,
        \Magetrend\PdfTemplates\Helper\Data $dataHelper
    ) {
        $this->templateFactory = $templateFactory;
        $this->moduleHelper = $dataHelper;
    }

    /**
     * Delete order comment template
     *
     * @param $templateId
     * @return bool
     */
    public function deleteTemplate($templateId)
    {
        $template = $this->templateFactory->create()
            ->load($templateId);
        if (!$template->getId()) {
            return false;
        }

        $template->delete();
        return true;
    }

    /**
     * Save template data
     *
     * @param $data
     * @param bool $templateId
     * @return mixed
     */
    public function saveTemplate($data, $templateId = false)
    {
        $template = $this->templateFactory->create();
        if ($templateId) {
            $template->load($templateId);
        }

        $template->addData([
            'name' => $data['name'],
            'comment' => $data['comment'],
            'store_ids' => $this->moduleHelper->encodeArray($data['store_ids']),
            'type' => $this->moduleHelper->encodeArray($data['type']),
        ]);

        $template->save();
        return $template;
    }

    /**
     * Returns template
     *
     * @param bool $templateId
     * @return \Magetrend\PdfTemplates\Model\Template
     */
    public function getTemplate($templateId = false)
    {
        $template = $this->templateFactory->create();
        if ($templateId) {
            $template->load($templateId);
        }
        return $template;
    }
}
