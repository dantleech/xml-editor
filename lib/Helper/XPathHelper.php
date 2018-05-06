<?php

namespace Phpactor\XmlQuery\Helper;

use Phpactor\XmlQuery\Exception\InvalidQueryValue;
use Phpactor\XmlQuery\Exception\MissingQueryParameter;

class XPathHelper
{
    public static function parameterizeQuery(string $query, array $params = array (
)): string
    {
        $chars = [];
        $paramIndex = 0;
        $isQuoted = false;
        $quote = null;
        $escaped = false;
        $lastChar = null;
        for ($i = 0; $i < strlen($query); $i++) {
            $char = $query[$i];

            if (false === $isQuoted && in_array($char, [ '"', "'" ])) {
                $isQuoted = true;
                $quote = $char;
                $chars[] = $char;
                continue;
            }

            if (false === $escaped && $char === $quote) {
                $isQuoted = false;
                $quote = null;
                $chars[] = $char;
                continue;
            }

            if (false === $isQuoted && $char === '?') {
                if (!isset($params[$paramIndex])) {
                    throw new MissingQueryParameter(sprintf(
                        'Parameter %d of expression "%s" is missing',
                        $paramIndex, $query
                    ));
                }

                $char = self::quoteValue($params[$paramIndex++]);
            }

            $chars[] = $char;
        }

        return implode('', $chars);
    }

    private static function quoteValue($value)
    {
        if (is_string($value)) {
            return '"' . str_replace('"', '\"', $value) . '"';
        }

        if (is_numeric($value)) {
            return $value;
        }

        throw new InvalidQueryValue(sprintf(
            'Query value of type "%s" is not recognized',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }
}
