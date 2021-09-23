<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\WidgetPlus\App\Action;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * Class ContextPlugin
 */
class ContextPlugin
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var
     */
    protected $visitor;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @param Session $customerSession
     * @param HttpContext $httpContext
     */
    public function __construct(
        Session $customerSession,
        HttpContext $httpContext,
        Visitor $visitor
    ) {
        $this->customerSession = $customerSession;
        $this->visitor = $visitor;
        $this->httpContext = $httpContext;
    }

    /**
     * Set customer group and customer session id to HTTP context
     *
     * @param AbstractAction $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(AbstractAction $subject, RequestInterface $request)
    {
        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            $customerId = 0;
        }

        $this->httpContext->setValue(
            'customer_logged_id',
            $customerId,
            false
        );

        // $visitorId = $this->visitor->create()->getId();
        // var_dump($visitorId);
        // //exit();
        // if (!$visitorId) {
        //     $visitorId = 0;
        // }
        //
        // $this->httpContext->setValue(
        //     'customer_visitor_id',
        //     $visitorId,
        //     false
        // );
    }

    // public function afterDispatch(AbstractAction $subject, $result, RequestInterface $request)
    // {
    //     $visitorId = $this->visitor->getId();
    //     var_dump($visitorId);
    //     //exit();
    //     if (!$visitorId) {
    //         $visitorId = 0;
    //     }
    //
    //     $this->httpContext->setValue(
    //         'customer_visitor_id',
    //         $visitorId,
    //         false
    //     );
    //
    //     return $result;
    // }
}
