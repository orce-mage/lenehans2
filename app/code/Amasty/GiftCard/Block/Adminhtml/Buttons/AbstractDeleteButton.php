<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Block\Adminhtml\Buttons;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

abstract class AbstractDeleteButton implements ButtonProviderInterface
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
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        Escaper $escaper
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
    }

    /**
     * Return button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $id = (int)$this->request->getParam($this->getIdField());

        if ($id) {
            $alertMessage = $this->escaper->escapeHtml(__('Are you sure you want to do this?'));
            $onClick = sprintf('deleteConfirm("%s", "%s")', $alertMessage, $this->getDeleteUrl($id));

            return [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => $onClick,
                'sort_order' => 30,
            ];
        }

        return [];
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public function getDeleteUrl(int $id): string
    {
        return $this->urlBuilder->getUrl('*/*/delete', [$this->getIdField() => $id]);
    }

    /**
     * @return string
     */
    abstract protected function getIdField(): string;
}
