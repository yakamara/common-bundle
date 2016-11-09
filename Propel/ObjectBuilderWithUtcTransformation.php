<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\Propel;

class ObjectBuilderWithUtcTransformation extends ObjectBuilder
{
    protected function createDateFromDb($var): string
    {
        return 'new \\Yakamara\\DateTime\\Date('.$var.')';
    }

    protected function createTimeFromDb($var): string
    {
        return '\\Yakamara\\DateTime\\DateTime::createUtc('.$var.')';
    }

    protected function createDateTimeFromDb($var): string
    {
        return '\\Yakamara\\DateTime\\DateTime::createFromUtc('.$var.')';
    }
}
