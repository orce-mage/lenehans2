<?php

namespace Amasty\Faq\Api\ImportExport;

interface ExportInterface
{
    const QUESTION_EXPORT = 'faq_question_export';

    const CATEGORY_EXPORT = 'faq_category_export';

    const EXPORT_TYPES = [self::QUESTION_EXPORT, self::CATEGORY_EXPORT];

    const BLOCK_NAME = 'faq.export';
}
