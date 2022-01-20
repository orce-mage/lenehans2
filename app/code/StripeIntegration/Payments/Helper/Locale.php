<?php

namespace StripeIntegration\Payments\Helper;

use Psr\Log\LoggerInterface;

class Locale
{
    public function __construct(
        \Magento\Framework\Locale\Resolver $localeResolver
    ) {
        $this->localeResolver = $localeResolver;
    }

    public function getLocale()
    {
        return $this->localeResolver->getLocale();
    }

    public function getKlarnaLocale()
    {
        return strtolower(str_replace("_", "-", $this->localeResolver->getLocale()));
    }

    public function getStripeJsLocale()
    {
        $supportedValues = ["ar", "bg", "cs", "da", "de", "el", "en", "en-GB", "es", "es-419", "et", "fi", "fr", "fr-CA", "he", "hu", "id", "it", "ja", "lt", "lv", "ms", "mt", "nb", "nl", "pl", "pt-BR", "pt", "ro", "ru", "sk", "sl", "sv", "tr", "zh", "zh-HK", "zh-TW"];

        $locale = $this->localeResolver->getLocale();
        if (empty($locale))
            return "auto";

        $hyphenLocale = str_replace($locale, "_", "-");
        if (in_array($hyphenLocale, $supportedValues))
            return $hyphenLocale;

        $lang = strstr($locale, '_', true);
        if (in_array($lang, $supportedValues))
            return $lang;

        return "auto";
    }

    public function getStripeCheckoutLocale()
    {
        $supportedValues = ['bg', 'cs', 'da', 'de', 'el', 'en', 'en-GB', 'es', 'es-419', 'et', 'fi', 'fil', 'fr', 'fr-CA', 'hr', 'hu', 'id', 'it', 'ja', 'ko', 'lt', 'lv', 'ms', 'mt', 'nb', 'nl', 'pl', 'pt', 'pt-BR', 'ro', 'ru', 'sk', 'sl', 'sv', 'th', 'tr', 'vi', 'zh', 'zh-HK', 'zh-TW'];

        $locale = $this->localeResolver->getLocale();
        if (empty($locale))
            return "auto";

        $hyphenLocale = str_replace($locale, "_", "-");
        if (in_array($hyphenLocale, $supportedValues))
            return $hyphenLocale;

        $lang = strstr($locale, '_', true);
        if (in_array($lang, $supportedValues))
            return $lang;

        return "auto";
    }
}
