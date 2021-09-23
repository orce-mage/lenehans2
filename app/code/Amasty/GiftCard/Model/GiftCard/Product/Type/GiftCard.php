<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard\Product\Type;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\Image\Repository;
use Amasty\GiftCard\Model\Config\Source\GiftCardType;
use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class GiftCard extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    const TYPE_AMGIFTCARD = 'amgiftcard';
    const CUSTOM_IMAGE_PARAM = 'custom';
    const CUSTOM_AMOUNT_PARAM = 'custom'; //used only for admin reorder\order \w gift-card
    const IMAGE_INPUT_NAME = 'amgiftcard-userimage-input';
    const FLAT_CATALOG_PRODUCT = 'catalog/frontend/flat_catalog_product';

    /**
     * @var bool
     */
    protected $_canUseQtyDecimals = false;

    /**
     * @var bool
     */
    protected $_canConfigure = true;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var FileUpload
     */
    private $fileUpload;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * @var Repository
     */
    private $imageRepository;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\RequestInterface $request,
        FileUpload $fileUpload,
        PriceCurrencyInterface $priceCurrency,
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        FormatInterface $localeFormat,
        Repository $imageRepository,
        DateTime $date,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository,
            $serializer
        );
        $this->request = $request;
        $this->fileUpload = $fileUpload;
        $this->priceCurrency = $priceCurrency;
        $this->configProvider = $configProvider;
        $storeManager->setCurrentStore(null); //reinitialize current store because default store saved incorrect
        $this->store = $storeManager->getStore();
        $this->localeFormat = $localeFormat;
        $this->imageRepository = $imageRepository;
        $this->date = $date;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if gift card type is combined
     *
     * @param Product $product
     * @return bool
     */
    public function isTypeCombined(Product $product): bool
    {
        return $product->getAmGiftcardType() == GiftCardType::TYPE_COMBINED;
    }

    /**
     * Check if gift card type is printed
     *
     * @param Product $product
     * @return bool
     */
    public function isTypePrinted(Product $product): bool
    {
        return $product->getAmGiftcardType() == GiftCardType::TYPE_PRINTED;
    }

    /**
     * Check if gift card type is virtual
     *
     * @param Product $product
     * @return bool
     */
    public function isTypeVirtual(Product $product): bool
    {
        return $product->getAmGiftcardType() == GiftCardType::TYPE_VIRTUAL;
    }

    public function isVirtual($product)
    {
        if ($option = $product->getCustomOption(GiftCardOptionInterface::GIFTCARD_TYPE)) {
            return $option->getValue() == GiftCardType::TYPE_VIRTUAL;
        } //after combined gcard validation bought type stored in custom option

        return $product->getAmGiftcardType() == GiftCardType::TYPE_VIRTUAL;
    }

    public function isSalable($product)
    {
        if (!$this->configProvider->isEnabled()) {
            return false;
        }
        $amounts = $product->getPriceModel()->getAmounts($product);
        $open = $product->getAmAllowOpenAmount();

        if (!$open && !$amounts) {
            return false;
        }

        return parent::isSalable($product);
    }

    protected function _prepareProduct(DataObject $buyRequest, $product, $processMode)
    {
        if ($productBuyRequest = $buyRequest->getData('info_buyRequest')) {//for totals recollect
            $buyRequestData = $this->serializer->unserialize($productBuyRequest);
            $buyRequest->addData($buyRequestData);
        }
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);

        if (is_string($result)) {
            return $result;
        }
        $isExistFile = false;

        if ($this->request->getFiles(self::IMAGE_INPUT_NAME)
            && $this->request->getFiles(self::IMAGE_INPUT_NAME)['name']
        ) {
            $isExistFile = true;
        }

        if ($isExistFile && !$buyRequest->getAmGiftcardImage()) { //new image uploaded
            $file = $this->request->getFiles(self::IMAGE_INPUT_NAME);
            try {
                $image = $this->fileUpload->saveFileToTmpDir($file, self::IMAGE_INPUT_NAME);
                $buyRequest->setAmGiftcardImage(self::CUSTOM_IMAGE_PARAM);
                //custom image field will be removed while conversation to order item
                $buyRequest->setAmGiftcardCustomImage($image['file']);
            } catch (LocalizedException $e) {
                return $e->getMessage();
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                return __('Something went wrong with image uploading.');
            }
        } elseif (is_numeric($buyRequest->getAmGiftcardImage())) { //changed from custom image to predefined
            $buyRequest->setAmGiftcardCustomImage('');
        } elseif ($buyRequest->getAmGiftcardCustomImage()) { //custom image left while editing product
            $buyRequest->setAmGiftcardImage(self::CUSTOM_IMAGE_PARAM);
        }

        try {
            $amount = $this->validate($buyRequest, $product, $processMode);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $e->getMessage();
        } catch (\Exception $e) {
            $this->_logger->critical($e);

            return __('An error has occurred while preparing Gift Card.');
        }
        $this->updateBuyRequest($product, $buyRequest);

        if ($amount > 0) { //in wishlist card can be with 0 amount => don't need to save custom option
            $product->addCustomOption(GiftCardOptionInterface::GIFTCARD_AMOUNT, $amount, $product);
        }
        $product->addCustomOption(GiftCardOptionInterface::IMAGE, $buyRequest->getAmGiftcardImage(), $product);

        if ($buyRequest->getAmGiftcardImage() == self::CUSTOM_IMAGE_PARAM) {
            $product->addCustomOption(
                GiftCardOptionInterface::CUSTOM_IMAGE,
                $buyRequest->getAmGiftcardCustomImage(),
                $product
            );
        }
        $product->addCustomOption(GiftCardOptionInterface::GIFTCARD_TYPE, $product->getAmGiftcardType(), $product);

        if (!$this->isTypePrinted($product)) {
            $product->addCustomOption(
                GiftCardOptionInterface::RECIPIENT_EMAIL,
                $buyRequest->getData(GiftCardOptionInterface::RECIPIENT_EMAIL),
                $product
            );
        }

        foreach ($this->configProvider->getGiftCardFields() as $field) {
            switch ($field) {
                case GiftCardOptionInterface::DELIVERY_DATE:
                    if ($buyRequest->getData(GiftCardOptionInterface::IS_DATE_DELIVERY) == 0) {
                        break;
                    }
                    $chosenDate = strtotime($buyRequest->getData($field)
                        . ' ' . $buyRequest->getData(GiftCardOptionInterface::DELIVERY_TIMEZONE));
                    $date = $this->date->gmtDate(null, $chosenDate);
                    $product->addCustomOption($field, $date, $product);
                    $product->addCustomOption(
                        GiftCardOptionInterface::DELIVERY_TIMEZONE,
                        $buyRequest->getData(GiftCardOptionInterface::DELIVERY_TIMEZONE),
                        $product
                    );
                    break;
                case GiftCardOptionInterface::RECIPIENT_NAME:
                    if (!$this->isTypePrinted($product)) {
                        $product->addCustomOption($field, $buyRequest->getData($field), $product);
                    }
                    break;
                default:
                    $product->addCustomOption($field, $buyRequest->getData($field), $product);
            }
        }

        return $result;
    }

    /**
     * @param Product $product
     * @param DataObject $buyRequest
     *
     * @return void
     */
    private function updateBuyRequest(Product $product, DataObject $buyRequest)
    {
        $productBuyRequest = $product->getCustomOption('info_buyRequest');
        $buyRequestData = $this->serializer->unserialize($productBuyRequest->getValue());

        if ($buyRequest->getAmGiftcardImage() == self::CUSTOM_IMAGE_PARAM
            && $buyRequest->getAmGiftcardCustomImage()
        ) {
            $buyRequestData[GiftCardOptionInterface::CUSTOM_IMAGE] = $buyRequest->getAmGiftcardCustomImage();
            $buyRequestData[GiftCardOptionInterface::IMAGE] = self::CUSTOM_IMAGE_PARAM;
        } else {
            unset($buyRequestData[GiftCardOptionInterface::CUSTOM_IMAGE]);
        }

        if ($buyRequest->getAmGiftcardAmountCustom()) {
            $buyRequestData[GiftCardOptionInterface::GIFTCARD_AMOUNT] = self::CUSTOM_AMOUNT_PARAM;
            $buyRequestData[GiftCardOptionInterface::CUSTOM_GIFTCARD_AMOUNT] = $buyRequest->getAmGiftcardAmountCustom();
        }
        unset($buyRequestData['am_giftcard_amount_custom_order']);

        $productBuyRequest->setValue($this->serializer->serialize($buyRequestData));
    }

    /**
     * @param DataObject $buyRequest
     * @param Product $product
     * @param string $processMode
     *
     * @return float
     * @throws LocalizedException
     */
    private function validate(DataObject $buyRequest, Product $product, string $processMode): float
    {
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);

        if ($this->isTypeCombined($product) && $buyRequest->getAmGiftcardType()) {
            $product->setAmGiftcardType($buyRequest->getAmGiftcardType());
        }
        $this->checkFields($buyRequest, $product, $isStrictProcessMode); //check for gcard fields filled

        if (!$product->getCustomOption(GiftCardOptionInterface::GIFTCARD_AMOUNT)) {
            $amount = $this->validateAmount($buyRequest, $product, $isStrictProcessMode);
        } else {
            $amount = (float)$product->getCustomOption(GiftCardOptionInterface::GIFTCARD_AMOUNT)
                ->getValue();
        }

        if ($isStrictProcessMode) {
            $this->checkImages($buyRequest, $product);
            $this->validateFields($buyRequest, $product);
        }

        return $amount;
    }

    protected function validateAmount(DataObject $buyRequest, Product $product, bool $isStrictProcessMode): float
    {
        $allowedAmounts = $this->getAllowedAmounts($product);
        $allowedOpenAmount = $product->getAmAllowOpenAmount();
        $selectedAmount = $buyRequest->getAmGiftcardAmount();
        $customAmount = $this->getCustomGiftcardAmount($buyRequest);

        $amount = null;
        if ((!$selectedAmount || $selectedAmount == self::CUSTOM_AMOUNT_PARAM) && $allowedOpenAmount) {
            if ($customAmount <= 0 && $isStrictProcessMode) {
                throw new LocalizedException(__('Please specify a gift card amount.'));
            }
            $amount = $this->validateCustomAmount($product, $customAmount, $isStrictProcessMode);
            $buyRequest->setAmGiftcardAmountCustom($amount);
        } elseif (is_numeric($selectedAmount)) {
            if (in_array($selectedAmount, $allowedAmounts)) {
                $amount = $selectedAmount;
            }
        }

        if ($amount === null) {
            $amount = $this->getSingleAmount($product);
        }

        return (float)$amount;
    }

    /**
     * If gift card have only one amount trying to receive it
     *
     * @param Product $product
     *
     * @return float
     */
    protected function getSingleAmount(Product $product): float
    {
        $allowed = $this->getAllowedAmounts($product);
        $amount = 0.;

        if (count($allowed) == 1) {
            $amount = array_shift($allowed);
        }

        return (float)$amount;
    }

    /**
     * @param Product $product
     * @param float $customAmount
     * @param bool $strictProcess
     *
     * @return float
     * @throws LocalizedException
     */
    protected function validateCustomAmount(Product $product, float $customAmount, bool $strictProcess): float
    {
        $min = $product->getAmOpenAmountMin();
        $max = $product->getAmOpenAmountMax();

        if ($strictProcess) {
            if ($min && $customAmount < $min) {
                throw new LocalizedException(
                    __('Gift Card min amount is %1', $this->priceCurrency->convertAndFormat($min, false))
                );
            }
            if ($max && $customAmount > $max) {
                throw new LocalizedException(
                    __('Gift Card max amount is %1', $this->priceCurrency->convertAndFormat($max, false))
                );
            }
        }

        return $customAmount;
    }

    /**
     * Get giftcard custom amount
     *
     * @param DataObject $buyRequest
     *
     * @return float
     */
    protected function getCustomGiftcardAmount(DataObject $buyRequest): float
    {
        $customAmount = $buyRequest->getAmGiftcardAmountCustom();

        if ($buyRequest->getAmGiftcardAmount() == self::CUSTOM_AMOUNT_PARAM
            && !$buyRequest->getAmGiftcardAmountCustomOrder()
        ) {
            return (float)$customAmount;
        }
        $rate = $this->store->getCurrentCurrencyRate();

        if ($rate != 1 && $customAmount) {
            $customAmount = $this->localeFormat->getNumber($customAmount);

            if (is_numeric($customAmount) && $customAmount) {
                $customAmount = $this->priceCurrency->round($customAmount / $rate);
            }
        }

        return (float)$customAmount;
    }

    /**
     * Check for empty fields
     *
     * @param DataObject $buyRequest
     * @param Product $product
     * @param bool $isStrictProcess
     *
     * @return void
     * @throws LocalizedException
     */
    private function checkFields(DataObject $buyRequest, Product $product, bool $isStrictProcess)
    {
        if (!$isStrictProcess) {
            return;
        }
        $emptyFields = 0;
        $availableFields = $this->configProvider->getGiftCardFields();
        $isTypePhysical = $this->isTypePrinted($product);

        foreach ($availableFields as $field) {
            switch ($field) {
                case GiftCardOptionInterface::MESSAGE:
                    break;
                case GiftCardOptionInterface::DELIVERY_DATE:
                    if ($buyRequest->getData(GiftCardOptionInterface::IS_DATE_DELIVERY) == 0) {
                        break;
                    }
                    if (!$buyRequest->getData($field)) {
                        $emptyFields++;
                    }
                    if (!$buyRequest->getData(GiftCardOptionInterface::DELIVERY_TIMEZONE)) {
                        $emptyFields++;
                    }
                    break;
                case GiftCardOptionInterface::RECIPIENT_NAME:
                    if ($isTypePhysical) {
                        break;
                    }

                    if (!$buyRequest->getData($field)) {
                        $emptyFields++;
                    }

                    if (!$buyRequest->getData(GiftCardOptionInterface::RECIPIENT_EMAIL)) {
                        $emptyFields++;
                    }
                    break;
                default:
                    if (!$buyRequest->getData($field)) {
                        $emptyFields++;
                    }
            }
        }

        if ($emptyFields > 1) {
            throw new LocalizedException(
                __('Please specify all the required information.')
            );
        }
    }

    /**
     * @param DataObject $buyRequest
     * @param Product $product
     *
     * @return void
     * @throws LocalizedException
     */
    private function validateFields(DataObject $buyRequest, Product $product)
    {
        $availableFields = $this->configProvider->getGiftCardFields();
        $isTypePhysical = $this->isTypePrinted($product);

        if (in_array(GiftCardOptionInterface::SENDER_NAME, $availableFields)
            && !$buyRequest->getData(GiftCardOptionInterface::SENDER_NAME)
        ) {
            throw new LocalizedException(__('Please specify a sender name.'));
        }

        if (in_array(GiftCardOptionInterface::DELIVERY_DATE, $availableFields)
            && $buyRequest->getData(GiftCardOptionInterface::IS_DATE_DELIVERY) != 0
        ) {
            $date = $buyRequest->getData(GiftCardOptionInterface::DELIVERY_DATE);

            if (!$date) {
                throw new LocalizedException(__('Please specify a delivery date.'));
            }
            $isValid = $this->date->timestamp($date)
                && ($this->date->timestamp($date) - $this->date->timestamp($this->date->date('m/d/Y')) >= 0);

            if (!$isValid) {
                throw new LocalizedException(__('Please specify a valid delivery date.'));
            }
            $timezone = $buyRequest->getData(GiftCardOptionInterface::DELIVERY_TIMEZONE);

            if (!$timezone) {
                throw new LocalizedException(__('Please specify a delivery timezone.'));
            }
        }

        if (!$isTypePhysical) {
            if (in_array(GiftCardOptionInterface::RECIPIENT_NAME, $availableFields)
                && !$buyRequest->getData(GiftCardOptionInterface::RECIPIENT_NAME)
            ) {
                throw new LocalizedException(__('Please specify a recipient name.'));
            }
            if ($email = $buyRequest->getData(GiftCardOptionInterface::RECIPIENT_EMAIL)) {
                $isValid = (bool)preg_match(
                    "/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix",
                    $email
                );
                if (!$isValid) {
                    throw new LocalizedException(__('Please specify a valid recipient email.'));
                }
            } else {
                throw new LocalizedException(__('Please specify a recipient email.'));
            }
        }
    }

    /**
     * Check chosen images
     * @param DataObject $buyRequest
     * @param Product $product
     *
     * @return void
     * @throws LocalizedException
     */
    protected function checkImages(DataObject $buyRequest, Product $product)
    {
        $userUploadAllowed = $this->configProvider->isAllowUserImages();
        $chosenImage = $buyRequest->getAmGiftcardImage();
        $customImage = $buyRequest->getAmGiftcardCustomImage();

        if ($chosenImage == self::CUSTOM_IMAGE_PARAM && $userUploadAllowed) {
            if (!$customImage) {
                throw new LocalizedException(
                    __('Please choose gift card image.')
                );
            }
        } elseif (is_numeric($chosenImage)) {
            $image = $this->imageRepository->getById((int)$chosenImage);

            if ($this->scopeConfig->getValue(self::FLAT_CATALOG_PRODUCT)) {
                $product = $this->productRepository->getById($product->getEntityId());
            }
            $allowedImageIds = explode(',', (string)$product->getAmGiftcardCodeImage());

            if (!in_array($image->getImageId(), $allowedImageIds) && $this->request->getActionName() !== 'reorder') {
                throw new LocalizedException(
                    __('Could not find selected image.')
                );
            }
        } elseif (!$chosenImage && !$customImage) {
            throw new LocalizedException(
                __('Please choose gift card image.')
            );
        }
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    protected function getAllowedAmounts(Product $product): array
    {
        $allowedAmounts = [];

        foreach ($product->getAmGiftcardPrices() as $amount) {
            $allowedAmounts[] = $this->priceCurrency->round($amount['value']);
        }

        return $allowedAmounts;
    }

    public function checkProductBuyState($product)
    {
        parent::checkProductBuyState($product);
        $option = $product->getCustomOption('info_buyRequest');

        if ($option instanceof \Magento\Quote\Model\Quote\Item\Option) {
            $buyRequest = new DataObject($this->serializer->unserialize($option->getValue()));
            $this->validate($buyRequest, $product, self::PROCESS_MODE_FULL);
        }
    }

    /**
     * @param Product $product
     * @param DataObject $buyRequest
     *
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $amount = $buyRequest->getData(GiftCardOptionInterface::GIFTCARD_AMOUNT);

        return [
            GiftCardOptionInterface::GIFTCARD_AMOUNT => is_numeric($amount)
                ? $this->priceCurrency->convertAndRound($amount) : $amount,
            GiftCardOptionInterface::CUSTOM_GIFTCARD_AMOUNT => $this->priceCurrency->convertAndRound(
                $buyRequest->getData(GiftCardOptionInterface::CUSTOM_GIFTCARD_AMOUNT) ?: 0
            ),
            GiftCardOptionInterface::IMAGE => $buyRequest->getData(
                GiftCardOptionInterface::IMAGE
            ),
            GiftCardOptionInterface::CUSTOM_IMAGE => $buyRequest->getData(
                GiftCardOptionInterface::CUSTOM_IMAGE
            ),
            GiftCardOptionInterface::MESSAGE => $buyRequest->getData(
                GiftCardOptionInterface::MESSAGE
            ),
            GiftCardOptionInterface::RECIPIENT_NAME => $buyRequest->getData(
                GiftCardOptionInterface::RECIPIENT_NAME
            ),
            GiftCardOptionInterface::RECIPIENT_EMAIL => $buyRequest->getData(
                GiftCardOptionInterface::RECIPIENT_EMAIL
            ),
            GiftCardOptionInterface::SENDER_NAME => $buyRequest->getData(
                GiftCardOptionInterface::SENDER_NAME
            ),
            GiftCardOptionInterface::GIFTCARD_TYPE => $buyRequest->getData(
                GiftCardOptionInterface::GIFTCARD_TYPE
            ),
            GiftCardOptionInterface::DELIVERY_DATE => $buyRequest->getData(
                GiftCardOptionInterface::DELIVERY_DATE
            ),
            GiftCardOptionInterface::DELIVERY_TIMEZONE => $buyRequest->getData(
                GiftCardOptionInterface::DELIVERY_TIMEZONE
            ),
            GiftCardOptionInterface::IS_DATE_DELIVERY => $buyRequest->getData(
                GiftCardOptionInterface::IS_DATE_DELIVERY
            )
        ];
    }

    /**
     * Delete data specific for Gift Card product type
     *
     * @param Product $product
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function deleteTypeSpecificData(Product $product)
    {
        return $this;
    }
}
