<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Import\Validator;

interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
    const ERROR_INVALID_PHOTO = 'invalidPhoto';

    const ERROR_NAME_IS_EMPTY = 'emptyName';

    const ERROR_ID_IS_EMPTY = 'emptyId';

    const ERROR_COUNTRY_IS_EMPTY = 'emptyCountry';
    
    const ERROR_MEDIA_URL_NOT_ACCESSIBLE = 'cantGetPhoto';

    const ENCODING_ERROR = 'encodingError';

    const ERROR_GOOGLE_GEO_DATA = 'geoDataError';

    const API_STATUS_ZERO_RESULTS = 'ZERO_RESULTS';

    const API_STATUS_OVER_DAILY_LIMIT = 'OVER_DAILY_LIMIT';

    const API_STATUS_OVER_QUERY_LIMIT = 'OVER_QUERY_LIMIT';

    const API_STATUS_REQUEST_DENIED = 'REQUEST_DENIED';

    const API_STATUS_INVALID_REQUEST = 'INVALID_REQUEST';

    const API_STATUS_UNKNOWN_ERROR = 'UNKNOWN_ERROR';

    /**
     * Initialize validator
     *
     * @param $context
     * @return $this
     */
    public function init($context);
}
