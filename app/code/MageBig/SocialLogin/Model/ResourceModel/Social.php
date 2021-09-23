<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SocialLogin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Social
 *
 * @package MageBig\SocialLogin\Model\ResourceModel
 */
class Social extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('magebig_social_customer', 'social_customer_id');
    }
}