<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales\Shipping;

use Magento\Framework\View\Element\Template;

class Tracking extends \Magetrend\Email\Block\Email\Block\Template
{
    public $templateVarManager;

    public function __construct(
        Template\Context $context,
        \Magetrend\Email\Model\TemplateVarManager $templateVarManager,
        array $data = []
    )
    {
        $this->templateVarManager = $templateVarManager;
        parent::__construct($context, $data);
    }

    public function getShipment()
    {
        return $this->getParentBlock()->getShipment();
    }

    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    public function getTrackUrl($item)
    {
        return $this->templateVarManager->getTrackinkLinkByItem($item);
    }
}
