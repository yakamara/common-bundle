<?php

namespace Yakamara\CommonBundle\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
            $date = $class::createFromFormat($options['format'], $value);

            if (!$date) {
                throw new NotFoundHttpException('Invalid date given.');
            }
        } else {
            if (false === strtotime($value)) {
                throw new NotFoundHttpException('Invalid date given.');
            }

            $date = new $class($value);
        }

        $request->attributes->set($param, $date);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        return is_subclass_of($configuration->getClass(), DateTimeInterface::class);
    }
}
