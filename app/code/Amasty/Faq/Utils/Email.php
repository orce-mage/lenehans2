<?php

namespace Amasty\Faq\Utils;

use Amasty\Faq\Model\ConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Email
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Send email helper
     * emailTo and sendFrom can be array with keys email and name.
     * Otherwise string with key to Store Email address.
     *
     * @param string|array $emailTo
     * @param string $templateConfigPath
     * @param array  $vars
     * @param string $area
     * @param string|array $sendFrom
     */
    public function sendEmail(
        $emailTo = '',
        $templateConfigPath = '',
        $vars = [],
        $area = \Magento\Framework\App\Area::AREA_FRONTEND,
        $sendFrom = ''
    ) {
        try {
            $storeId = null;
            if (isset($vars['asked_from_store'])) {
                $storeId = $vars['asked_from_store'];
            }
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->storeManager->getStore($storeId);
            $data =  array_merge(
                [
                    'website_name'  => $store->getWebsite()->getName(),
                    'group_name'    => $store->getGroup()->getName(),
                    'store_name'    => $store->getName(),
                ],
                $vars
            );

            if (empty($sendFrom)) {
                $sendFrom = 'general';
            }

            if (!is_array($emailTo)) {
                $emailTo = [
                    'email' => $this->scopeConfig->getValue(
                        'trans_email/ident_' . $emailTo . '/email',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $store->getId()
                    ),
                    'name' => $this->scopeConfig->getValue(
                        'trans_email/ident_' . $emailTo . '/name',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $store->getId()
                    )
                ];
            }

            $transport = $this->transportBuilder->setTemplateIdentifier(
                $this->scopeConfig->getValue(
                    ConfigProvider::PATH_PREFIX . $templateConfigPath,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store->getId()
                )
            )->setTemplateOptions(
                ['area' => $area, 'store' => $store->getId()]
            )->setTemplateVars(
                $data
            )->setFrom(
                $sendFrom
            )->addTo(
                $emailTo['email'],
                $emailTo['name']
            )->getTransport();

            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
