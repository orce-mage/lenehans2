<?php

namespace Amasty\Faq\Api\ImportExport;

interface QuestionInterface extends \Amasty\Faq\Api\Data\QuestionInterface
{
    const QUESTION = 'question';

    const STORE_CODES = 'store_codes';

    const PRODUCT_SKUS = 'product_skus';

    const CATEGORY_IDS = 'category_ids';
}
