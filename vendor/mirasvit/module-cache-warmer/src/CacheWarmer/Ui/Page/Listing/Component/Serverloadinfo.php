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



namespace Mirasvit\CacheWarmer\Ui\Page\Listing\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;
use Mirasvit\CacheWarmer\Model\Config;
use Mirasvit\CacheWarmer\Service\Rate\ServerLoadRateService;

class Serverloadinfo extends AbstractComponent
{
    /**
     * @var ServerLoadRateService
     */
    private $serverLoadRateService;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ServerLoadRateService  $serverLoadRateService
     * @param Config                 $config
     * @param ContextInterface       $context
     * @param array $components
     * @param array                  $data
     */
    public function __construct(
        ServerLoadRateService $serverLoadRateService,
        Config $config,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->serverLoadRateService = $serverLoadRateService;
        $this->config            = $config;

        parent::__construct($context, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentName()
    {
        return 'server_load';
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $config = $this->getData('config');

        $config['fillServerHistory'] = $this->serverLoadRateService->getHistory();

        $this->setData('config', $config);

        parent::prepare();
    }
}
