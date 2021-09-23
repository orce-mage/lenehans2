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

namespace Magetrend\PdfTemplates\Model\Pdf;

/**
 * String decorator class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Decorator
{
    const TYPE_COLOR = 'color';

    public function addDecorator($text, $type, $attributeCode)
    {
        $decoratedText = '{{decorate '.$type.'="'.$attributeCode.'"}}'.$text.'{{/decorate}}';
        return $decoratedText;
    }

    public function getDecorators($string)
    {
        if (strpos($string, '{{decorate ') === false) {
            return [];
        }

        $decorators = [];
        $offset = 0;
        $string = $this->removeInvisibleSymbols($string);
        $split = explode('{{decorate ', $string);
        $offset = 0;
        foreach ($split as $key => $value) {
            if (strpos($value, '{{/decorate}}') === false) {
                $offset += mb_strlen($value);
                continue;
            }

            $decoratedString = explode('{{/decorate}}', $value);
            $attr = explode('}}', $decoratedString[0]);
            $options = explode('="', $attr[0]);

            if (!isset($options[1])) {
                return [];
            }
            $options[1] = rtrim($options[1], '"');

            $length = mb_strlen($attr[1]);
            $decorators[] = [
                'type' => $options[0],
                'text' => $attr[1],
                'attribute' => $options[1],
                'offset' => $offset,
                'pos' => $length,
            ];

            $offset = $offset + $length;
            if (isset($decoratedString[1])) {
                $offset += mb_strlen($decoratedString[1]);
            }
        }
        return $decorators;
    }

    public function removeDecorators($string)
    {
        if (strpos($string, '{{decorate ') !== false) {
            $split = explode('{{decorate ', $string);
            foreach ($split as $key => $value) {
                if (strpos($value, '"}}') === false) {
                    continue;
                }
                $tmpString = explode('}}', $value);
                unset($tmpString[0]);
                $value = implode('}}', $tmpString);
                $value = str_replace('{{/decorate}}', '', $value);
                $split[$key] = $value;
            }

            $string = implode('', $split);
        }

        if (strpos($string, '{{/decorate}}') !== false) {
            $string = str_replace('{{/decorate}}', '', $string);
        }

        return $string;
    }

    /**
     * @param $pdfPage
     * @param  \Magetrend\PdfTemplates\Model\Pdf\Element $element
     * @param $decorator
     */
    public function applyDecoration($pdfPage, $element, $decorator)
    {
        $attributes = $element->getAttributes();
        if (!isset($attributes[$decorator['attribute']])) {
            return false;
        }

        if ($decorator['type'] == self::TYPE_COLOR) {
            $color = $element->getPdfColor($attributes[$decorator['attribute']]);
            if (get_class($pdfPage) == 'TCPDF') {
                $pdfPage->setColor('text', $color[0], $color[1], $color[2]);
            } else {
                $pdfPage->setFillColor($color);
            }
        }

        return true;
    }

    /**
     * @param $pdfPage
     * @param  \Magetrend\PdfTemplates\Model\Pdf\Element $element
     */
    public function resetDecoration($pdfPage, $element)
    {
        $color = $element->getFillColor();
        if (get_class($pdfPage) == 'TCPDF') {
            $pdfPage->setColor('text', $color[0], $color[1], $color[2]);
        } else {
            $pdfPage->setFillColor($color);
        }
    }

    public function removeInvisibleSymbols($string)
    {
        $string = str_replace(['<br/>', '<br>', '</br>', "\n", '{br}'], '', $string);
        return $string;
    }
}
