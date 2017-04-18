<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class TextExtension extends \Twig_Extension
{
    private $converter;

    public function __construct(CamelCaseToSnakeCaseNameConverter $converter)
    {
        $this->converter = $converter;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('snake_case', [$this->converter, 'normalize']),
            new \Twig_SimpleFilter('camel_case', [$this->converter, 'denormalize']),
        ];
    }

    public function getName()
    {
        return 'yakamara_text_extension';
    }
}
