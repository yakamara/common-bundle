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

use Yakamara\CommonBundle\Security\SecurityContext;

class SecurityExtension extends \Twig_Extension
{
    protected $security;

    public function __construct(SecurityContext $security)
    {
        $this->security = $security;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('switched_user', function () {
                return $this->security->isUserSwitched();
            }),
            new \Twig_SimpleFunction('switched_user_source', function () {
                return $this->security->getSwitchedUserSource();
            }),
        ];
    }

    public function getName()
    {
        return 'yakamara_security_extension';
    }
}
