<?php
declare(strict_types=1);

namespace Amasty\Faq\Model\Emails\Notifier;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\OptionSource\Question\Status;
use Amasty\Faq\Model\OptionSource\Question\Visibility;
use Amasty\Faq\Model\Url;
use Amasty\Faq\Utils\Email;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerNotifier implements NotifierInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Email
     */
    private $email;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Url
     */
    private $url;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Email $email,
        ConfigProvider $configProvider,
        Url $url
    ) {
        $this->productRepository = $productRepository;
        $this->email = $email;
        $this->configProvider = $configProvider;
        $this->url = $url;
    }

    public function notify(QuestionInterface $question): void
    {
        if (!$this->configProvider->isNotifyUser()) {
            return;
        }
        $vars = [
            'customer_name' => $question->getName() ? : __('Customer'),
            'question' => $question->getTitle(),
            'answer' => strip_tags(
                $question->getCleanedFullAnswer(),
                '<ul><li><p><br>'
            ),
            'question_link' => $this->getQuestionUrl($question),
            'asked_from_store' => $question->getAskedFromStore()
        ];
        $productData = $this->getProductData($question);
        if (!empty($productData)) {
            $vars = array_merge($vars, $productData);
        }

        $this->email->sendEmail(
            [
                'email' => $question->getEmail(),
                'name' => $question->getName()
            ],
            ConfigProvider::USER_NOTIFY_EMAIL_TEMPLATE,
            $vars,
            Area::AREA_FRONTEND,
            $this->configProvider->getNotifySender($question->getAskedFromStore())
        );
    }

    private function getProductData(QuestionInterface $question): array
    {
        $productIds = $question->getProductIds();
        if (!$productIds) {
            return [];
        }

        $productIds = explode(',', $productIds);
        $productId = $productIds[array_key_first($productIds)];
        $storeId = $question->getAskedFromStore();

        try {
            $product = $this->productRepository->getById($productId, false, $storeId);

            return [
                'product_name' => $product->getName(),
                'product_link' => $product->getProductUrl()
            ];
        } catch (NoSuchEntityException $exception) {
            return [];
        }
    }

    private function getQuestionUrl(QuestionInterface $question): ?string
    {
        if ($question->getUrlKey()
            && $question->getStatus() == Status::STATUS_ANSWERED
            && $question->getVisibility() == Visibility::VISIBILITY_PUBLIC
        ) {
            return $this->url->getQuestionUrl($question);
        }

        return null;
    }
}
