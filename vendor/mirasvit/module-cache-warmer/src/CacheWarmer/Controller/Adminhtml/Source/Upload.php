<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */




namespace Mirasvit\CacheWarmer\Controller\Adminhtml\Source;


use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Mirasvit\CacheWarmer\Api\Repository\SourceRepositoryInterface;
use Mirasvit\CacheWarmer\Controller\Adminhtml\AbstractSource;
use Magento\Backend\App\Action\Context;
use Mirasvit\CacheWarmer\Helper\Serializer;
use Mirasvit\CacheWarmer\Service\SourceFileUploaderService;

class Upload extends AbstractSource
{
    public $uploaderService;

    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        Registry $registry,
        Context $context,
        Serializer $serializer,
        SourceFileUploaderService $uploaderService
    ) {
        $this->uploaderService = $uploaderService;

        parent::__construct($sourceRepository, $registry, $context, $serializer);
    }

    public function execute()
    {
        $result = $this->uploaderService->save('file');
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
