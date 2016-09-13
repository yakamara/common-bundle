<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Yakamara\DateTime\Date;
use Yakamara\DateTime\DateTime;
use Yakamara\DateTime\DateTimeInterface;

class DateTimeParamConverter implements ParamConverterInterface
{
    public function apply(Request $request, ParamConverter $configuration)
    {
        $param = $configuration->getName();

        if (!$request->attributes->has($param)) {
            return false;
        }

        $options = $configuration->getOptions();
        $value = $request->attributes->get($param);

        if (!$value && $configuration->isOptional()) {
            return false;
        }

        $class = $configuration->getClass();

        if (isset($options['format'])) {
            $class = DateTimeInterface::class === $class ? DateTime::class : $class;
            $dateTime = $class::createFromFormat($options['format'], $value);

            if (!$dateTime) {
                throw new NotFoundHttpException('Invalid date given.');
            }
        } else {
            if (DateTimeInterface::class === $class) {
                $class = preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? Date::class : DateTime::class;
            }

            $dateTime = $this->isSubclass($class, Date::class) ? new $class($value) : $class::createFromFormat('Y-m-d-H-i-s', $value);
        }

        $request->attributes->set($param, $dateTime);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        return $this->isSubclass($configuration->getClass(), DateTimeInterface::class);
    }

    private function isSubclass(string $class1, string $class2): bool
    {
        return $class1 === $class2 || is_subclass_of($class1, $class2);
    }
}
