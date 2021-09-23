<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Controller\Index;

use Magento\Framework\App\Action\Context;

class Run extends \Magento\Framework\App\Action\Action
{
    /** @var \Amasty\Orderexport\Helper\Data $_helper */
    protected $_helper;
    /** @var \Amasty\Orderexport\Model\Profiles $_profiles */
    protected $_profiles;

    /**
     * Run constructor.
     * @param Context $context
     * @param \Amasty\Orderexport\Helper\Data $helper
     * @param \Amasty\Orderexport\Model\Profiles $profiles
     */
    public function __construct(
        Context $context,
        \Amasty\Orderexport\Helper\Data $helper,
        \Amasty\Orderexport\Model\Profiles $profiles
    )
    {
        parent::__construct($context);
        $this->_helper = $helper;
        $this->_profiles = $profiles;
    }

    public function execute()
    {
        if (!$this->_helper->getModuleConfig('run_by_url/enabled')) {
            $this->getResponse()->setBody(__('Running by direct URL is disabled.'));
            return;
        }

        if ($id = $this->getRequest()->getParam('id'))
        {
            $code = $this->getRequest()->getParam('sec');
            if (!$code || $code != $this->_helper->getModuleConfig('run_by_url/sec_code')) {
                $this->getResponse()->setBody(__('Incorrect security code.'));
                return;
            }

            $profile = $this->_profiles->load($id);

            if ($profile->getId()) {
                try {
                    $profile->run(null);
                    $this->getResponse()->setBody(__('Successfully complete.'));
                } catch (\Exception $e) {
                    $this->getResponse()->setBody($e->getMessage());
                }
            } else {
                $this->getResponse()->setBody(__('Incorrect profile ID.'));
                return;
            }
        } else {
            $this->getResponse()->setBody(__('Profile ID not specified.'));
            return;
        }

    }
}
