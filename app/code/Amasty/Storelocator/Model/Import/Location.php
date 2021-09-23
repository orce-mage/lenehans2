<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Import;

use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Model\ConfigProvider;
use Amasty\Storelocator\Model\GalleryFactory;
use Amasty\Storelocator\Model\ImageProcessor;
use Amasty\Storelocator\Model\Import\Validator\RowValidatorInterface as ValidatorInterface;
use Amasty\Storelocator\Model\ResourceModel\Gallery;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

class Location extends AbstractEntity
{
    const VALUE_ALL_WEBSITES = 'All Websites';

    const COL_ID = 'id';

    const COL_NAME = 'name';

    const COL_COUNTRY = 'country';

    const COL_CITY = 'city';

    const COL_ZIP = 'zip';

    const COL_ADDRESS = 'address';

    const COL_STATE = 'state';

    const COL_DESCRIPTION = 'description';

    const COL_PHONE = 'phone';

    const COL_EMAIL = 'email';

    const COL_STORES = 'stores';

    const COL_STATUS = 'status';

    const COL_LOCATION_GALLERY = 'gallery_image';

    const COL_MARKER = 'marker_img';

    const COL_WEBSITE = 'website';

    const COL_LAT = 'lat';

    const COL_LNG = 'lng';

    const COL_POSITION = 'position';

    const COL_SHOW_SCHEDULE = 'show_schedule';

    const COL_SCHEDULE = 'schedule';

    const COL_URL_KEY = 'url_key';

    const COL_META_TITLE = 'meta_title';

    const COL_META_DESCRIPTION = 'meta_description';

    const VALIDATOR_MAIN = 'validator';

    const VALIDATOR_COUNTRY = 'country';

    const VALIDATOR_PHOTO = 'photo';

    const TABLE_AMASTY_LOCATION = 'amasty_amlocator_location';

    const TABLE_AMASTY_STORE_ATTRIBUTE = 'amasty_amlocator_store_attribute';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_INVALID_PHOTO            => 'Invalid Image or Marker, please check if '
            . 'file exists in the system',
        ValidatorInterface::ERROR_COUNTRY_IS_EMPTY         => 'Invalid Country',
        ValidatorInterface::ERROR_ID_IS_EMPTY              => 'Id Field Is Empty',
        ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE => 'can\'t access to Media Url',
        ValidatorInterface::ERROR_NAME_IS_EMPTY            => 'Name Is Empty',
        ValidatorInterface::ERROR_GOOGLE_GEO_DATA          => 'Error with getting geo data from google'
    ];

    /**
     * Google Maps API message statuses template definitions
     *
     * @var array
     */
    protected $apiStatusTemplates = [
        ValidatorInterface::API_STATUS_ZERO_RESULTS     =>
            'Google Maps Geocoder was passed a non-existent or invalid address.',
        ValidatorInterface::API_STATUS_OVER_DAILY_LIMIT => 'Something wrong with your API key or account billing',
        ValidatorInterface::API_STATUS_OVER_QUERY_LIMIT => 'You are over your request quota.',
        ValidatorInterface::API_STATUS_REQUEST_DENIED   => 'Request was denied by Google Maps API.',
        ValidatorInterface::API_STATUS_INVALID_REQUEST  => 'Some of required parameters are missing in query.',
        ValidatorInterface::API_STATUS_UNKNOWN_ERROR    =>
            'Request could not be processed due to a server error. Please try again.'
    ];

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = true;

    private $counter = 1;

    private $autoIncrement;

    private $locationId;

    /**
     * Columns for locations
     *
     * @var array
     */
    protected $locationColumnNames = [
        self::COL_ID,
        self::COL_NAME,
        self::COL_COUNTRY,
        self::COL_CITY,
        self::COL_ZIP,
        self::COL_ADDRESS,
        self::COL_STATE,
        self::COL_DESCRIPTION,
        self::COL_PHONE,
        self::COL_EMAIL,
        self::COL_STORES,
        self::COL_STATUS,
        self::COL_LOCATION_GALLERY,
        self::COL_MARKER,
        self::COL_WEBSITE,
        self::COL_LAT,
        self::COL_LNG,
        self::COL_POSITION,
        self::COL_SHOW_SCHEDULE,
        self::COL_SCHEDULE,
        self::COL_URL_KEY,
        self::COL_META_TITLE,
        self::COL_META_DESCRIPTION
    ];

    /**
     * Valid column names
     *
     * @var array
     */
    protected $validColumnNames = [];

    protected $coordinatesFields = [
        self::COL_COUNTRY,
        self::COL_CITY,
        self::COL_STATE,
        self::COL_ZIP,
        self::COL_ADDRESS
    ];

    /**
     * @var \Amasty\Storelocator\Model\Import\Proxy\Location\ResourceModelFactory
     */
    protected $resourceFactory;

    /**
     * Media files uploader
     *
     * @var \Magento\CatalogImportExport\Model\Import\Uploader
     */
    protected $fileUploader;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var array
     */
    protected $validators = [];

    /**
     * Column names that holds images files names
     *
     * @var string[]
     */
    protected $imagesArrayKeys = ['photo'];

    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    private $readFactory;

    /**
     * @var \Magento\Framework\HTTP\Adapter\CurlFactory
     */
    private $curlFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection
     */
    private $attributeCollection;

    /**
     * @var \Amasty\Storelocator\Model\LocationFactory
     */
    private $locationFactory;

    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    private $dataHelper;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var Gallery
     */
    private $galleryResource;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var GalleryFactory
     */
    protected $galleryFactory;

    /**
     * @var Photo
     */
    protected $validatorPhoto;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var array
     */
    private $rowsWithEmptyResponse = [];

    /**
     * @var Http
     */
    private $request;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $importPath = null;

    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\File\ReadFactory $readFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Proxy\Location\ResourceModelFactory $resourceModelFactory,
        Validator\Country $validatorCountry,
        Validator\Photo $validatorPhoto,
        \Amasty\Storelocator\Model\ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory,
        \Amasty\Storelocator\Model\LocationFactory $locationFactory,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        Validator $validator,
        Serializer $serializer,
        ImageUploader $imageUploader,
        ImageProcessor $imageProcessor,
        GalleryFactory $galleryFactory,
        ConfigProvider $configProvider,
        Gallery $galleryResource,
        Http $request
    ) {
        $this->rootDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->uploaderFactory = $uploaderFactory;
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_connection = $resource->getConnection('write');
        $tableInfo = $this->_connection->showTableStatus($resource->getTableName(self::TABLE_AMASTY_LOCATION));
        $this->autoIncrement = $tableInfo['Auto_increment'];
        $this->validators[self::VALIDATOR_MAIN] = $validator->init($this);
        //add validators for Country and Image
        $this->validators[self::VALIDATOR_PHOTO] = $validatorPhoto;
        $this->validatorPhoto = $validatorPhoto;
        $this->validators[self::VALIDATOR_COUNTRY] = $validatorCountry;
        $this->errorAggregator = $errorAggregator;
        foreach (array_merge($this->errorMessageTemplates, $this->_messageTemplates) as $errorCode => $message) {
            $this->getErrorAggregator()->addErrorMessageTemplate($errorCode, $message);
        }
        $this->scopeConfig = $scopeConfig;
        $this->readFactory = $readFactory;
        $this->curlFactory = $curlFactory;
        $this->resourceFactory = $resourceModelFactory;
        $this->attributeCollection = $attributeCollectionFactory->create();
        $this->locationFactory = $locationFactory;
        $this->dataHelper = $dataHelper;
        $this->initLocatorColumns();
        $this->imageUploader = $imageUploader;
        $this->imageProcessor = $imageProcessor;
        $this->galleryFactory = $galleryFactory;
        $this->galleryResource = $galleryResource;
        $this->configProvider = $configProvider;
        $this->serializer = $serializer;
        $this->request = $request;
        $this->filesystem = $filesystem;
    }

    protected function _getValidator($type)
    {
        return $this->validators[$type];
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'amasty_storelocator';
    }

    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int   $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $this->_validatedRows[$rowNum] = true;
        // BEHAVIOR_DELETE use specific validation logic
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (!isset($rowData[self::COL_ID])) {
                $this->addRowError(ValidatorInterface::ERROR_ID_IS_EMPTY, $rowNum);
                return false;
            }
            return true;
        }

        $this->_processedEntitiesCount++;

        if (!$this->_getValidator(self::VALIDATOR_MAIN)->isValid($rowData)) {
            foreach ($this->_getValidator(self::VALIDATOR_MAIN)->getMessages() as $message) {
                $this->addRowError($message, $rowNum);
            }
        }

        if (!$this->_getValidator(self::VALIDATOR_COUNTRY)->isValid($rowData)) {
            foreach ($this->_getValidator(self::VALIDATOR_MAIN)->getMessages() as $message) {
                $this->addRowError($message, $rowNum);
            }
        }

        foreach ($rowData as $value) {
            if (!mb_check_encoding($value)) {
                $this->addRowError(ValidatorInterface::ENCODING_ERROR, $rowNum);
            }
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Validate data rows and save bunches to DB
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _saveValidatedBunches()
    {
        $source = $this->_getSource();
        $currentDataSize = 0;
        $bunchRows = [];
        $startNewBunch = false;
        $nextRowBackup = [];
        $maxDataSize = $this->_resourceHelper->getMaxDataSize();
        $bunchSize = $this->_importExportData->getBunchSize();
        $idSet = [];

        $source->rewind();
        $this->_dataSourceModel->cleanBunches();

        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || !$source->valid()) {
                $this->_dataSourceModel->saveBunch($this->getEntityTypeCode(), $this->getBehavior(), $bunchRows);

                $bunchRows = $nextRowBackup;
                $currentDataSize = strlen($this->serializer->serialize($bunchRows));
                $startNewBunch = false;
                $nextRowBackup = [];
            }
            if ($source->valid()) {
                try {
                    $rowData = $source->current();
                    $idSet[$rowData['id']] = true;
                } catch (\InvalidArgumentException $e) {
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }

                $this->_processedRowsCount++;

                if ($this->validateRow($rowData, $source->key())) {
                    // add row to bunch for save
                    $rowData = $this->_prepareRowForDb($rowData);
                    $rowSize = strlen($this->jsonHelper->jsonEncode($rowData));

                    $isBunchSizeExceeded = $bunchSize > 0 && count($bunchRows) >= $bunchSize;

                    if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                        $startNewBunch = true;
                        $nextRowBackup = [$source->key() => $rowData];
                    } else {
                        $bunchRows[$source->key()] = $rowData;
                        $currentDataSize += $rowSize;
                    }
                }
                $source->next();
            }
        }
        $this->_processedEntitiesCount = count($idSet);

        return $this;
    }

    /**
     * Returns an object for upload a media files
     *
     * @return \Magento\CatalogImportExport\Model\Import\Uploader
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUploader()
    {
        if ($this->fileUploader === null) {
            $this->fileUploader = $this->uploaderFactory->create();

            $this->fileUploader->init();

            $dirConfig = DirectoryList::getDefaultConfig();
            $dirAddon = $dirConfig[DirectoryList::MEDIA][DirectoryList::PATH];

            $DS = DIRECTORY_SEPARATOR;

            $tmpPath = $this->mediaDirectory->getAbsolutePath(ImageProcessor::AMLOCATOR_MEDIA_TMP_PATH) . $DS
                . $this->locationId;

            if (!$this->fileUploader->setTmpDir($tmpPath)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('File directory \'%1\' is not readable.', $tmpPath)
                );
            }
            $destinationDir = "amasty/amlocator/tmp";
            $destinationPath =
                $dirAddon . $DS . $this->mediaDirectory->getRelativePath($destinationDir) . $DS . $this->locationId;

            $this->rootDirectory->create($destinationPath);
            if (!$this->fileUploader->setDestDir($destinationPath)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('File directory \'%1\' is not writable.', $destinationPath)
                );
            }
        }
        return $this->fileUploader;
    }

    /**
     * Uploading files into the "amasty/amlocator/tmp media folder.
     * Return a new file name if the same file is already exists.
     *
     * @param string $fileName
     * @return string
     */
    protected function uploadMediaFiles($fileName, $renameFileOff = false)
    {
        try {
            $this->getUploader()->move($fileName, $renameFileOff);
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function _importData()
    {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteLocation();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->saveAndReplaceLocations();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveAndReplaceLocations();
        }

        if ($this->rowsWithEmptyResponse) {
            foreach ($this->rowsWithEmptyResponse as $rowNumber => $message) {
                $this->getErrorAggregator()->addError(
                    ValidatorInterface::ERROR_GOOGLE_GEO_DATA,
                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL,
                    $rowNumber,
                    null,
                    $message
                );
            }
        }

        return true;
    }

    /**
     * Deletes Advanced price data from raw data.
     *
     * @return $this
     */
    public function deleteLocation()
    {
        $listIds = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $listIds[] = $rowData[self::COL_ID];
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        $tableName = $this->resourceFactory->create()->getTable(self::TABLE_AMASTY_LOCATION);
        try {
            $this->countItemsDeleted += $this->_connection->delete(
                $tableName,
                $this->_connection->quoteInto(self::COL_ID . ' IN (?)', $listIds)
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Save and replace Locations
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function saveAndReplaceLocations()
    {
        $behavior = $this->getBehavior();
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
            $this->_cachedSkuToDelete = null;
        }
        $locationsRows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $locations = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$locationsRows) {
                    $locationsRows = $this->getRows($rowData);
                }
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_ID_IS_EMPTY, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $locations[$rowNum] = $this->prepareData($rowData, $locationsRows, $rowNum);
            }
            $this->saveLocation($locations, self::TABLE_AMASTY_LOCATION);
        }

        return $this;
    }

    /**
     * Getting rows for saving
     *
     * @param array $locationData
     *
     * @return array $locationsRows
     */
    private function getRows($locationData)
    {
        $locationsRows = [];
        foreach ($locationData as $key => $value) {
            $locationsRows[] = $key;
        }

        if (!isset($locationData[self::COL_LAT])) {
            $locationsRows[] = self::COL_LAT;
        }

        if (!isset($locationData[self::COL_LNG])) {
            $locationsRows[] = self::COL_LNG;
        }

        return $locationsRows;
    }

    /**
     * Save product prices.
     *
     * @param array  $locations
     * @param string $table
     *
     * @return $this
     */
    protected function saveLocation(array $locations, $table)
    {
        $tableName = $this->resourceFactory->create()->getTable($table);
        $delLocationIds = [];
        foreach ($locations as $location) {
            if (isset($location[self::COL_ID])) {
                $delLocationIds[] = $location[self::COL_ID];
            }
        }
        if (Import::BEHAVIOR_APPEND != $this->getBehavior()) {
            $this->_connection->delete(
                $tableName,
                $this->_connection->quoteInto('id IN (?)', $delLocationIds)
            );
        }

        foreach ($locations as $location) {
            $locationModel = $this->locationFactory->create();
            if (!empty($location[self::COL_ID])) {
                $locationModel->load($location[self::COL_ID]);
                if (!$locationModel->getId()) {
                    $locationModel->setModelFlags();
                }
            }
            $locationModel->addData($location);
            $locationModel->save();
        }

        return $this;
    }

    protected function checkCountry($country)
    {
        /** @var \Amasty\Storelocator\Model\Import\Validator\Country $validator */
        $validator = $this->_getValidator(self::VALIDATOR_COUNTRY);
        if (strlen($country) > 2) {
            $country = $validator->getCountryByName($country);
        }
        return $country;
    }

    /**
     * @param string $region
     * @param string $country
     *
     * @return int|string
     */
    private function checkState($region, $country)
    {
        /** @var \Amasty\Storelocator\Model\Import\Validator\Country $validator */
        $validator = $this->_getValidator(self::VALIDATOR_COUNTRY);

        if (strlen($region) > 0) {
            $region = $validator->getRegionByName($region, $country);
        }

        return $region;
    }

    public function getCoordinates($address, $rowNum)
    {
        $apiKey = $this->configProvider->getApiKey();
        $query = [
            'sensor'  => 'false',
            'address' => $address
        ];

        $url = "https://maps.google.com/maps/api/geocode/json?" . http_build_query($query) . '&key=' . $apiKey;
        $httpAdapter = $this->curlFactory->create();
        $httpAdapter->write(\Zend_Http_Client::GET, $url, '1.1', ['Connection: close']);
        $response = $httpAdapter->read();
        $body = \Zend_Http_Response::extractBody($response);
        //generate array object from the response from the web

        $coordinates = ['lat' => 0, 'lng' => 0];
        if ($body) {
            $json = $this->jsonHelper->jsonDecode($body);
            if ($json['status'] == 'OK' && isset($json['results'][0]['geometry']['location'])) {
                $coordinates = [
                    'lat' => $json['results'][0]['geometry']['location']['lat'],
                    'lng' => $json['results'][0]['geometry']['location']['lng']
                ];
            } else {
                $message = __(
                    'An error has happened while detecting geo-position data by Google. The error is: '
                    . $this->apiStatusTemplates[$json['status']]
                )->getText();
                $this->rowsWithEmptyResponse[$rowNum] = $message;
            }
        }

        return $coordinates;
    }

    /**
     * Initialize locator attributes
     */
    private function initLocatorColumns()
    {
        $this->validColumnNames = $this->locationColumnNames;
        foreach ($this->attributeCollection as $attribute) {
            $this->validColumnNames[] = $attribute->getAttributeCode();
        }
    }

    /**
     * Prepare data for saving
     *
     * @param array $locationData
     * @param array $locationsRows
     * @param int $rowNum
     *
     * @return array $locationData
     */
    private function prepareData($locationData, $locationsRows, $rowNum)
    {
        $attributeCodes = $this->attributeCollection->getColumnValues('attribute_code');
        foreach ($locationsRows as $key) {
            if (in_array($key, $attributeCodes)) {
                $attributeId = $this->attributeCollection
                    ->getItemByColumnValue('attribute_code', $key)
                    ->getAttributeId();
                $locationData['store_attribute'][$attributeId] = $locationData[$key];
            } else {
                $locationData = $this->prepareCustomRows($key, $locationData, $rowNum);
            }
        }

        return $locationData;
    }

    /**
     * Prepare custom rows
     *
     * @param string $key
     * @param array $locationData
     * @param int $rowNum
     *
     * @return array $locationData
     */
    private function prepareCustomRows($key, $locationData, $rowNum)
    {
        if (!isset($locationData[self::COL_LAT]) || !isset($locationData[self::COL_LNG])) {
            $addressInfo = [];
            foreach ($this->coordinatesFields as $coordinatesField) {
                if (isset($locationData[$coordinatesField])) {
                    $addressInfo[] = $locationData[$coordinatesField];
                }
            }
            $locationData = array_merge($locationData, $this->getCoordinates(implode(' ', $addressInfo), $rowNum));
        }
        switch ($key) {
            case self::COL_ID:
                if (empty($locationData[$key])) {
                    // set id = Auto_increment + 1
                    $locationData[$key] = $this->locationId = $this->autoIncrement + $this->counter;
                    $this->counter++;
                }
                $this->imageUploader->setBaseTmpPath(
                    ImageProcessor::AMLOCATOR_MEDIA_TMP_PATH . DIRECTORY_SEPARATOR . $locationData[$key]
                );
                break;
            case self::COL_COUNTRY:
                $locationData[$key] = $this->checkCountry($locationData[$key]);
                break;

            case self::COL_STATE:
                if (isset($locationData[self::COL_COUNTRY])) {
                    $locationData[$key] = $this->checkState($locationData[$key], $locationData[self::COL_COUNTRY]);
                }
                break;

            case self::COL_LOCATION_GALLERY:
                $fileNames = explode(',', $locationData[$key]);
                $locationData[$key] = [];
                foreach (array_filter($fileNames) as $file) {
                    $locationData[$key][] =
                        $this->moveImage($locationData[self::COL_ID], $file, $this->getImportPath());
                }
                break;

            case self::COL_MARKER:
                if (!empty($locationData[$key])) {
                    $markerInfo =
                        $this->moveImage($locationData[self::COL_ID], $locationData[$key], $this->getImportPath());
                    $locationData[$key] = $markerInfo['name'];
                }
                break;

            case self::COL_STORES:
                if (isset($locationData[$key])) {
                    if ($locationData[$key] == 'all') {
                        $locationData[$key] = ',0,';
                    } else {
                        $locationData[$key] = ',' . $locationData[$key] . ',';
                    }
                }
                break;
        }

        return $locationData;
    }

    /**
     * @param string $imageDir
     *
     * @return string
     */
    private function prepareImageDir($imageDir)
    {
        if ($imageDir[0] !== '/') {
            $imageDir = '/' . $imageDir;
        }
        if (substr($imageDir, -1) !== '/') {
            $imageDir = $imageDir . '/';
        }

        return $imageDir;
    }

    private function setImportPath()
    {
        $imagesDirectory = $this->request->getParam(\Magento\ImportExport\Model\Import::FIELD_NAME_IMG_FILE_DIR);
        $this->importPath = $this->prepareImageDir(
            $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($imagesDirectory)
        );
    }

    /**
     * @return string
     */
    public function getImportPath()
    {
        if ($this->importPath === null) {
            $this->setImportPath();
        }

        return $this->importPath;
    }

    /**
     * Move file
     *
     * @param string $importPath
     * @param string $file
     * @param int $rowNum
     *
     * @return array $fileInfo
     */
    private function moveImage($rowNum, $file, $importPath = '')
    {
        if ($this->validatorPhoto->checkFileExists($importPath . $file)) {
            $this->rootDirectory->copyFile(
                $this->rootDirectory->getAbsolutePath($importPath . $file),
                $this->mediaDirectory->getAbsolutePath(
                    $this->imageUploader->getBaseTmpPath() . DIRECTORY_SEPARATOR . $file
                )
            );

        } elseif ($this->validatorPhoto->checkValidUrl($file)) {
            $file = $this->uploadFile($file);
        }

        $fileInfo = [
            'name' => $file,
            'tmp_name' => $file
        ];

        return $fileInfo;
    }

    /**
     * Upload file
     *
     * @param string $url
     *
     * @return string $fileName
     */
    private function uploadFile($url)
    {
        $this->uploadMediaFiles($url, true);
        $urlParts = explode('/', $url);
        $fileName = array_pop($urlParts);

        return $fileName;
    }
}
