<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\ValueResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Yakamara\DateTime\Date;
use Yakamara\DateTime\DateTime;
use Yakamara\DateTime\DateTimeInterface;

final class DateTimeValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!is_a($argument->getType(), DateTimeInterface::class, true) || !$request->attributes->has($argument->getName())) {
            return [];
        }

        $value = $request->attributes->get($argument->getName());
        $class = DateTimeInterface::class === $argument->getType() ? DateTime::class : $argument->getType();

        if ($value instanceof DateTimeInterface) {
            return [$value];
        }

        if ($argument->isNullable() && !$value) {
            return [null];
        }

        $isDateTimeFormat = preg_match('/^\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}$/', $value);

        if (DateTimeInterface::class === $class) {
            $class = $isDateTimeFormat || 'now' === $value ? DateTime::class : Date::class;
        }

        $dateTime = $isDateTimeFormat ? $class::createFromFormat('Y-m-d-H-i-s', $value) : new $class($value);

        return [$dateTime];
    }
}
