<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */

namespace Magetrend\PdfTemplates\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * MT Editor manager class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class MtEditorManager
{
    /**
     * @var TemplateFactory
     */
    public $templateFactory;

    public $template;

    public $elementFactory;

    public $attributeFactory;

    public $elementCollectionFactory;

    public $attributeCollectionFactory;

    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    public $coreRegistry;

    public $config;

    public $fileUploaderFactory;

    public $adapterFactory;

    public $filesystem;

    public $simpleXml;

    public $jsonHelper;

    private $elementAttributeCollection = [];

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    public $assetRepo;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    public $readFactory;

    public $storeManager;

    /**
     * @var \Magetrend\PdfTemplates\Model\Pdf\Filter\Invoice
     */
    public $invoiceFilter;

    /**
     * @var Pdf\Filter\Creditmemo
     */
    public $creditmemoFilter;

    /**
     * @var Pdf\Filter\Shipment
     */
    public $shipmentFilter;

    /**
     * @var Pdf\Filter\Order
     */
    public $orderFilter;

    /**
     * @var Pdf\Filter\Quote
     */
    public $quoterFilter;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    public $orderRepository;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    public $invoiceRepository;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    public $shipmentRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    public $creditmemoRepository;

    /**
     * @var \Magento\Framework\File\UploaderFactory
     */
    public $uploaderFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    public $cartRepository;

    /**
     * @var TypeManager
     */
    public $typeManager;

    /**
     * MtEditorManager constructor.
     * @param Template $template
     * @param TemplateFactory $templateFactory
     * @param ElementFactory $elementFactory
     * @param AttributeFactory $attributeFactory
     * @param ResourceModel\Element\CollectionFactory $elementCollectionFactory
     * @param ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magetrend\PdfTemplates\Helper\Data $dataHelper
     * @param Config\Media\Config $config
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Simplexml\Config $simpleXml
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Pdf\Filter\Invoice $invoiceFilter
     * @param Pdf\Filter\Creditmemo $creditmemoFilter
     * @param Pdf\Filter\Shipment $shipmentFilter
     * @param Pdf\Filter\Order $orderFilter
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     */
    public function __construct(
        \Magetrend\PdfTemplates\Model\Template $template,
        \Magetrend\PdfTemplates\Model\TemplateFactory $templateFactory,
        \Magetrend\PdfTemplates\Model\ElementFactory $elementFactory,
        \Magetrend\PdfTemplates\Model\AttributeFactory $attributeFactory,
        \Magetrend\PdfTemplates\Model\ResourceModel\Element\CollectionFactory $elementCollectionFactory,
        \Magetrend\PdfTemplates\Model\ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magetrend\PdfTemplates\Helper\Data $dataHelper,
        \Magetrend\PdfTemplates\Model\Config\Media\Config $config,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Simplexml\Config $simpleXml,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magetrend\PdfTemplates\Model\TypeManager $typeManager
    ) {
        $this->templateFactory = $templateFactory;
        $this->moduleHelper = $dataHelper;
        $this->coreRegistry = $coreRegistry;
        $this->elementCollectionFactory = $elementCollectionFactory;
        $this->elementFactory = $elementFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attributeFactory = $attributeFactory;
        $this->config = $config;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->simpleXml = $simpleXml;
        $this->jsonHelper = $jsonHelper;
        $this->file = $file;
        $this->assetRepo = $assetRepo;
        $this->readFactory = $readFactory;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->template = $template;
        $this->uploaderFactory = $uploaderFactory;
        $this->cartRepository = $cartRepository;
        $this->typeManager = $typeManager;
    }

    public function createTemplate($params)
    {
        if (!isset($params['template_size'])) {
            $params['template_size'] = \Zend_Pdf_Page::SIZE_A4;
        }
        $template = $this->templateFactory->create()
            ->setData([
                'name' => $params['template_name'],
                'type' => $params['template_type'],
                'design' => $params['template_design'],
                'locale' => $params['template_locale'],
                'store_id' => $params['template_store_id'],
                'size' => $params['template_size'],
            ])->save();

        if (isset($params['template_design']) && !empty($params['template_design'])) {
            $templateType = $params['template_type'];
            $design = $params['template_design'];
            $size = $this->moduleHelper->getFolderBySize($params['template_size']);
            $fileName = $design.'.xml';
            $typeAdapter = $this->typeManager->setTemplateType($templateType)->getAdapter();
            $filePath = $typeAdapter->getModuleName().'::design/'.$typeAdapter->getType().'/'.$size.'/'.$fileName;
            $designPath = $this->assetRepo->createAsset($filePath)->getSourceFile();
            $this->importTemplate($template->getId(), str_replace($fileName, '', $designPath), $fileName);
        }

        return $template;
    }

    /**
     * Return template model by id
     *
     * @param $templateId
     * @return Template
     */
    public function initTemplate($templateId)
    {
        $model = $this->getTemplate($templateId);

        if (!$this->coreRegistry->registry('current_pdf_template')) {
            $this->coreRegistry->register('current_pdf_template', $model);
        }

        return $model;
    }

    /**
     * Returns template
     *
     * @param bool $templateId
     * @return \Magetrend\PdfTemplates\Model\Template
     */
    public function getTemplate($templateId = false)
    {
        $template = $this->templateFactory->create();
        if ($templateId) {
            $template->load($templateId);
        }
        return $template;
    }

    /**
     * Save template data
     *
     * @param bool $templateId
     * @param $elements
     * @return mixed
     */
    public function saveTemplate($templateId, $ppi, $elements, $templateOptions)
    {
        $template = $this->templateFactory->create();
        $template->load($templateId);
        if (!$template->getId()) {
            return false;
        }

        $template->addData($templateOptions)
            ->setPpi($ppi)
            ->setUpdatedAt(time())
            ->save();

        $elementMap = $this->getIndexedElementCollection($templateId);
        if (!empty($elements)) {
            foreach ($elements as $element) {
                $elementId = $element['id'];
                if (isset($elementMap[$elementId])) {
                    $this->updateElement($elementMap[$elementId], $element);
                    unset($elementMap[$elementId]);
                } else {
                    $this->createElement($element, $templateId);
                }
            }
        }

        if (!empty($elementMap)) {
            foreach ($elementMap as $element) {
                $this->removeElement($element);
            }
        }

        return $template;
    }

    /**
     * Delete element
     *
     * @param $element
     */
    public function removeElement($element)
    {
        $this->attributeCollectionFactory->create()
            ->addFieldToFilter('element_id', $element->getId())
            ->walk('delete');
        $element->delete();
    }

    /**
     * Create new element
     *
     * @param $elementData
     * @param $templateId
     */
    public function createElement($elementData, $templateId)
    {
        $element = $this->elementFactory->create()
            ->setData('template_id', $templateId)
            ->save();
        return $this->updateElement($element, $elementData);
    }

    /**
     * Create element attribute
     *
     * @param $elementId
     * @param $key
     * @param string $value
     */
    public function createAttribute($elementId, $key, $value = '')
    {
        $this->attributeFactory->create()
            ->setElementId($elementId)
            ->setAttributeKey($key)
            ->setAttributeValue($value)
            ->save();
    }

    /**
     * Update element attribute
     *
     * @param $elementId
     * @param $key
     * @param $value
     */
    public function updateAttribute($elementId, $key, $value)
    {
        $attributeCollection = $this->getElementAttributeCollection($elementId);
        if (isset($attributeCollection[$key])) {
            $attribute = $attributeCollection[$key];
            $attribute->setAttributeValue($value)
                ->save();
        } else {
            $this->createAttribute($elementId, $key, $value);
        }
    }

    /**
     * Ubdate element
     *
     * @param $element
     * @param $elementData
     */
    public function updateElement($element, $elementData)
    {
        $element->setUid($elementData['id'])
            ->setPageId($elementData['page_id'])
            ->setType($elementData['type'])
            ->setSortOrder($elementData['sort_order'])
            ->save();

        $elementId = $element->getId();
        if (!empty($elementData['attributes'])) {
            $attributeData = $elementData['attributes'];
            foreach ($attributeData as $key => $value) {
                $this->updateAttribute($elementId, $key, $value);
            }
        }
    }

    /**
     * Returns indexed element list. Index: uid
     *
     * @param $templateId
     * @return array
     */
    public function getIndexedElementCollection($templateId)
    {
        $templateElementCollection = $this->elementCollectionFactory->create()
            ->addFieldToFilter('template_id', $templateId);
        $elementMap = [];
        if ($templateElementCollection->getSize() > 0) {
            foreach ($templateElementCollection as $element) {
                $elementMap[$element->getUid()] = $element;
            }
        }
        return $elementMap;
    }

    /**
     * Returns element attributes
     *
     * @param $elementId
     * @return mixed
     */
    public function getElementAttributeCollection($elementId)
    {
        if (!isset($this->elementAttributeCollection[$elementId])) {
            $attributeCollection = $this->attributeCollectionFactory->create()
                ->addFieldToFilter('element_id', $elementId);
            $collectionData = [];
            if ($attributeCollection->getSize() > 0) {
                foreach ($attributeCollection as $attribute) {
                    $collectionData[$attribute->getAttributeKey()] = $attribute;
                }
            }
            $this->elementAttributeCollection[$elementId] = $collectionData;
        }
        return $this->elementAttributeCollection[$elementId];
    }

    /**
     * Process image upload
     *
     * @return string
     */
    public function uploadFiles()
    {
        $uploader = $this->fileUploaderFactory->create(['fileId' => 'files']);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        $imageAdapter = $this->adapterFactory->create();
        $uploader->addValidateCallback('email', $imageAdapter, 'validateUploadFile');
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $result = $uploader->save($mediaDirectory->getAbsolutePath($this->config->getBaseMediaPath()));
        $this->prepareImage($result);
        $fileUrl = $this->config->getMediaUrl($result['file']);
        return $fileUrl;
    }

    /**
     * Process file import
     *
     * @param $templateId
     * @return bool
     */
    public function importDataFile($templateId)
    {
        $file = $this->uploadDataFile();
        if ($file['error'] != 0) {
            return false;
        }
        return $this->importTemplate($templateId, $file['path'], $file['file']);
    }

    /**
     * Template import
     *
     * @param $templateId
     * @param $filePath
     * @param $fileName
     * @return bool
     */
    public function importTemplate($templateId, $filePath, $fileName)
    {
        $template = $this->templateFactory->create()
            ->load($templateId);

        $xml = $this->getDesignXml($template->getType(), $filePath, $fileName);
        if (!$xml) {
            return false;
        }

        $template->clean();
        $templateNode = $this->simpleXml->getNode();
        $template->addData([
            'ppi' => $templateNode->ppi,
            'footer_height' => $templateNode->footer_height,
            'header_height' => $templateNode->header_height,
            'hide_overflow' => $templateNode->hide_overflow,
            'design' => $templateNode->design,
        ])->save();

        $this->importElementData($template, $this->simpleXml->getNode('elements'));
    }

    /**
     * Prepare design and return SimpleXml object
     *
     * @param $filePath
     * @param $fileName
     * @return bool
     */
    public function getDesignXml($templateType, $filePath, $fileName)
    {
        $fileContent = $this->readFactory->create($filePath)
            ->readFile($fileName);

        $moduleName = $this->typeManager
            ->setTemplateType($templateType)
            ->getAdapter()
            ->getModuleName();

        $mediaUrl = $this->assetRepo->getUrl($moduleName.'::images/media/logo.jpg');
        $mediaUrl = str_replace('logo.jpg', '', $mediaUrl);
        $fileContent = str_replace($moduleName.'::images/media/', $mediaUrl, $fileContent);
        return $this->simpleXml->loadString($fileContent);
    }

    /**
     * Import element
     *
     * @param $template
     * @param $elements
     */
    public function importElementData($template, $elements)
    {
        if (count($elements) == 0) {
            return;
        }
        foreach ($elements->element as $element) {
            $newElemenet = $this->importElement($template, $element);
            $this->importAttributesData($newElemenet, $element->attributes);
        }
    }

    /**
     * Import Attributes
     *
     * @param $element
     * @param $attributes
     */
    public function importAttributesData($element, $attributes)
    {
        if (count($attributes) == 0) {
            return;
        }
        foreach ($attributes->attribute as $attribute) {
            $newElemenet = $this->importAttribute($element, $attribute);
        }
    }

    /**
     * Import Element
     *
     * @param $template
     * @param $element
     * @return mixed
     */
    public function importElement($template, $element)
    {
        $data = [];
        $skip = ['attributes', 'template_id', 'entity_id'];
        foreach ($element as $key => $value) {
            if (in_array($key, $skip)) {
                continue;
            }
            $data[$key] = (string)$value;
        }
        $element = $this->elementFactory->create()
            ->setData($data)
            ->setTemplateId($template->getId())
            ->save();

        return $element;
    }

    /**
     * Import attribute
     *
     * @param $element
     * @param $attribute
     * @return mixed
     */
    public function importAttribute($element, $attribute)
    {

        $data = [];
        $skip = ['element_id', 'entity_id'];
        foreach ($attribute as $key => $value) {
            if (in_array($key, $skip)) {
                continue;
            }
            $value = $this->prepareForImport($value);
            $data[$key] = (string)$value;
        }

        $newAttribute = $this->attributeFactory->create()
            ->setData($data)
            ->setElementId($element->getId())
            ->save();
        return $newAttribute;
    }

    public function prepareForImport($value)
    {
        if (substr_count($value, 'images/media/') == 1) {
            $value = explode('/images/media/', $value);
            $module = explode('/', $value[0]);
            $module = end($module);
            $value = $this->assetRepo
                ->createAsset($module.'::images/media/'.$value[1])
                ->getUrl();
        } elseif (substr_count($value, 'media/pdftemplates/') == 1) {
            $value = explode('media/pdftemplates/', $value);
            $value = rtrim(
                $this->storeManager->getStore(0)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA),
                '/'
            ).'/pdftemplates/'.$value[1];
        }

        return $value;
    }

    /**
     * Process file upload
     *
     * @return mixed
     */
    public function uploadDataFile()
    {
        $uploader = $this->uploaderFactory->create(['fileId' => 'files']);
        $uploader->setAllowedExtensions(['xml']);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $tmp = $this->filesystem->getDirectoryRead(DirectoryList::TMP)->getAbsolutePath();
        $result = $uploader->save($tmp);
        return $result;
    }

    /**
     * Prepare image for zend pdf
     *
     * @param $data
     */
    public function prepareImage($data)
    {
        if ($data['error'] > 0) {
            return;
        }

        if (substr_count(strtolower($data['file']), '.png') == 1) {
            $this->preparePngImage($data);
        }

        if (substr_count(strtolower($data['file']), '.jpg') == 1) {
            $this->prepareJpgImage($data);
        }
    }

    /**
     * Prepare jpg for zend pdf
     *
     * @param $data
     */
    public function prepareJpgImage($data)
    {
        $src = $data['path'].'/'.$data['file'];
        if ($this->imageIsCMYK($src)) {
            $this->jpgRgb($src);
        }
    }

    /**
     * Convert jpg to rgb jpg
     *
     * @param $src
     */
    public function jpgRgb($src)
    {
        $tmp_image = imagecreatefromjpeg($src);
        imagejpeg( $tmp_image, $src);
        imagedestroy($tmp_image);
    }

    /**
     * Prepare png image for zend pdf
     *
     * @param $data
     */
    public function preparePngImage($data)
    {
        $src = $data['path'].'/'.$data['file'];
        list($width, $height) = getimagesize($src);
        $im = imagecreatetruecolor($width,$height);
        $src_ = imagecreatefrompng($src);
        $alpha_channel = imagecolorallocatealpha($im, 255, 255, 255, 127);
        imagecolortransparent($im, $alpha_channel);
        imagefill($im, 0, 0, $alpha_channel);
        imagecopy($im,$src_, 0, 0, 0, 0, $width, $height);
        imagesavealpha($im, true);
        imagepng($im, $src, 0);
        imagedestroy($im);
    }

    /**
     * Returns has image CMYK color space
     *
     * @param $path
     * @return bool
     */
    public function imageIsCMYK($path) {
        $t = getimagesize($path);
        if (array_key_exists('mime', $t) and 'image/jpeg' == $t['mime']) {
            if (array_key_exists('channels', $t) and 4 == $t['channels']) {
                return true;
            }
        }
        return false;
    }

    public function getAvailableVariableList($templateId, $sourceId)
    {
        $template = $this->templateFactory->create()->load($templateId);
        if (!$template->getId()) {
            throw new \Exception(__('Bad template ID'));
        }

        $typeAdapter = $this->typeManager
            ->setTemplateType($template->getType())
            ->getAdapter();

        $sourceObject = $typeAdapter->getObjectById($sourceId);

        $filter =  $this->getFilter($template)->setSource($sourceObject);
        $variables = $filter->getData();

        $shippingAddressKeys = array_keys($filter->addShippingData([]));
        $billingAddressKeys = array_keys($filter->addBillingData([]));

        $groupedVariables = [
            'shipping' => [],
            'billing' => [],
            'other' => [],
        ];

        $ignoreVar = [
            'entity_id', 'parent_id', 'customer_address_id', 'quote_address_id', 'region_id', 'customer_id',
            's_entity_id', 's_parent_id', 's_customer_address_id', 's_quote_address_id', 's_region_id', 's_customer_id',
            'vat_is_valid', 'vat_request_id', 'vat_request_date', 'vat_request_success',
            's_vat_is_valid', 's_vat_request_id', 's_vat_request_date', 's_vat_request_success',
            'address_type', 's_address_type',
        ];

        foreach ($variables as $key => $value) {
            if (in_array($key, $ignoreVar)) {
                continue;
            } elseif (in_array($key, $shippingAddressKeys)) {
                $groupedVariables['shipping'][$key] = $value;
            } elseif (in_array($key, $billingAddressKeys)) {
                $groupedVariables['billing'][$key] = $value;
            } else {
                $groupedVariables['other'][$key] = $value;
            }
        }

        return $groupedVariables;
    }

    public function getFilter($template)
    {
        return $this->typeManager
            ->setTemplateType($template->getType())
            ->getAdapter()
            ->getFilter();

        throw new \Exception(__('Unknow source type'));
    }

    public function getDesignList($type, $size)
    {
        $colection = $this->template->getDesignCollection($type, $size);
        if (empty($colection)) {
            return [];
        }
        $designList = [];
        foreach ($colection as $item) {
            $designList[$item->getName()] = $item->getLabel();
        }

        return $designList;
    }
}
