<?php

namespace Yakamara\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class AbstractController extends Controller
{
    public function isUserSwitched()
    {
        return $this->get('yakamara_common.switch_user_checker')->isUserSwitched();
    }
}
