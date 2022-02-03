<?php
declare(strict_types=1);

namespace Amasty\Faq\Model\Emails\Notifier;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Utils\Email;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\NoSuchEntityException;

class AdminNotifier implements NotifierInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Email
     */
    private $email;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        ConfigProvider $configProvider,
        Email $email,
        ProductRepositoryInterface $productRepository
    ) {
        $this->configProvider = $configProvider;
        $this->email = $email;
        $this->productRepository = $productRepository;
    }

    public function notify(QuestionInterface $question): void
    {
        if (!$this->configProvider->isNotifyAdmin()) {
            return;
        }
        $emailData = [
            'sender_name' => $question->getName(),
            'sender_email' => $question->getEmail(),
            'question' => $question->getTitle()
        ];
        $productIds = $question->getProductIds();

        if ($productIds) {
            try {
                $product = $this->productRepository->getById((int)$productIds);
                $emailData['product_url'] = $product->getProductUrl();
            } catch (NoSuchEntityException $e) {
                ; //nothing to do
            }
        }

        $this->email->sendEmail(
            $this->configProvider->notifyAdminEmail(),
            ConfigProvider::ADMIN_NOTIFY_EMAIL_TEMPLATE,
            $emailData,
            Area::AREA_ADMINHTML
        );
    }
}
