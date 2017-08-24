<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\Util;

class SearchQueryConverter
{
    public static function convert(string $query): ?string
    {
        $parts = [];
        foreach (str_getcsv(trim($query), ' ') as $part) {
            $op = '-' === $part[0] ? '-' : '+';
            $part = ltrim($part, '+- ');
            $part = str_replace(['"', '+', '-', '~', '(', ')', '<', '>'], ' ', $part);

            if (mb_strlen($part) <= 2) {
                continue;
            }

            if (preg_match('/[ @*]/', $part)) {
                $part = '"'.$part.'"';
            } else {
                $part .= '*';
            }

            $parts[] = $op.$part;
        }

        if (!$parts) {
            return null;
        }

        return implode(' ', $parts);
    }
}
