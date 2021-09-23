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

namespace Magetrend\PdfTemplates\Api;

/**
 * Interface for PDFs
 * @api
 */
interface PdfManagementInterface
{
    /**
     * Get order by increment id
     *
     * @param string $incrementId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrder($incrementId);

    /**
     * Get invoice by increment id
     *
     * @param string $incrementId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getInvoice($incrementId);

    /**
     * Get shipment by increment id
     *
     * @param string $incrementId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getShipment($incrementId);

    /**
     * Get creditmemo by increment id
     *
     * @param string $incrementId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCreditmemo($incrementId);
}
