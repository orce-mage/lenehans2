<?php

declare(strict_types=1);

namespace Amasty\Faq\Controller;

class RegistryRequestParamConstants
{
    const FAQ_TAG_PARAM = 'tag';
    const FAQ_QUERY_PARAM = 'query';
    const FAQ_SEARCH_PARAMS = [
        self::FAQ_TAG_PARAM,
        self::FAQ_QUERY_PARAM,
    ];
}
