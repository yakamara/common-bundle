<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\DependencyInjection;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait ServiceLocatorAwareTrait
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    #[Required]
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }
}
