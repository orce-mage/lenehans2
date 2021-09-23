<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\Adminhtml\Location\Edit;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 */
class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param RequestInterface $request
     * @param UrlInterface     $urlBuilder
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $id = (int)$this->request->getParam('id');
        if ($id) {
            $alertMessage = __('Are you sure you want to do this?');
            $onClick = sprintf('deleteConfirm("%s", "%s")', $alertMessage, $this->getDeleteUrl($id));
            return [
                'label'      => __('Delete'),
                'class'      => 'delete',
                'on_click'   => $onClick,
                'sort_order' => 30,
            ];
        }
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public function getDeleteUrl($id)
    {
        return $this->urlBuilder->getUrl('*/*/delete', ['id' => $id]);
    }
}
