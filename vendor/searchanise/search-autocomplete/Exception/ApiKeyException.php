<?php

namespace Searchanise\SearchAutocomplete\Exception;

/**
 * Invalid ApiKey exception
 */
class ApiKeyException extends \Exception
{
    public function __construct($message = '')
    {
        if (empty($message)) {
            $message = __('Searchanise: ApiKey is not valid');
        }

        return parent::__construct($message);
    }
}
