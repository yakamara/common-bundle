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

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TextExtension extends AbstractExtension
{
    private $converter;

    public function __construct()
    {
        $this->converter = new CamelCaseToSnakeCaseNameConverter();
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('snake_case', [$this->converter, 'normalize']),
            new TwigFilter('camel_case', [$this->converter, 'denormalize']),
        ];
    }
}
