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

class TextExtension extends \Twig_Extension
{
    private $converter;

    public function __construct()
    {
        $this->converter = new CamelCaseToSnakeCaseNameConverter();
    }

    public function getFilters(): array
    {
        return [
            new \Twig_Filter('snake_case', [$this->converter, 'normalize']),
            new \Twig_Filter('camel_case', [$this->converter, 'denormalize']),
        ];
    }
}
