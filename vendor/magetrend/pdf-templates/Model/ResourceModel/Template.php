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

namespace Magetrend\PdfTemplates\Model\ResourceModel;

/**
 * Template resource class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Template extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var String
     */
    private $pageTable;

    /**
     * Initialize resource model
     *
     * @return void
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_init('mt_pdftemplates_template', 'entity_id');
        $this->pageTable = $this->getTable('mt_pdftemplates_template_page');
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->saveAdditionalPage($object);
        return parent::_afterSave($object);
    }

    /**
     * Save additional pages
     * @param AbstractModel $object
     * @return $this
     */
    public function saveAdditionalPage(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->hasData('additional_page')) {
            return $this;
        }

        $additionalPage = $object->getAdditionalPage();
        $templateId = $object->getId();
        $connection = $this->getConnection();
        if ($object->getId()) {
            $condition = ['parent_template_id =?' => $templateId];
            $connection->delete($this->pageTable, $condition);
        }

        if (is_array($additionalPage)) {
            foreach ($additionalPage as $page) {
                $connection->insert($this->pageTable, [
                    'parent_template_id' => $templateId,
                    'template_id' => $page['template_id'],
                    'sort_order' => $page['sort_order']
                ]);
            }
        }

        return $this;
    }

    /**
     * Returns additional pages
     * @param $reviewId
     * @return array
     */
    public function getAdditionalPage($templateId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->pageTable, ['template_id', 'sort_order'])
            ->where('parent_template_id =?', $templateId)
            ->order('sort_order ASC');

        return $connection->fetchAll($select);
    }
}
