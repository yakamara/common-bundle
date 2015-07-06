<?php

namespace Yakamara\CommonBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Extension extends \Twig_Extension
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('percent', [$this, 'percent'], [
                'needs_environment' => true,
            ]),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('descriptive_date', [$this, 'descriptiveDate'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('icon', [$this, 'icon'], [
                'pre_escape' => 'html',
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('current_url', [$this, 'currentUrl']),
            new \Twig_SimpleFunction('email', [$this, 'email'], [
                'pre_escape' => 'html',
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('address', [$this, 'address'], [
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('gender', [$this, 'gender']),
            new \Twig_SimpleFunction('gender_name', [$this, 'genderName']),
        ];
    }

    public function percent(\Twig_Environment $env, $number, $decimal = null)
    {
        return \twig_number_format_filter($env, $number * 100, $decimal);
    }

    public function descriptiveDate(\Twig_Environment $env, $datetime)
    {
        if (!$datetime) {
            return '';
        }
        $datetime = \twig_date_converter($env, $datetime);
        $descriptiveDate = $this->container->get('yakamara_common.datetime')->descriptiveDateTime($datetime, $descriptive);
        if ($descriptive) {
            $descriptiveDate = '<span data-toggle="tooltip" title="' . $datetime->format('d.m.Y') . '&nbsp;' . $datetime->format('H:i') . '">' . $descriptiveDate . '</span>';
        }
        return $descriptiveDate;
    }

    public function icon($icon)
    {
        return '<i class="fa fa-'.$icon.'"></i>';
    }

    public function currentUrl(array $parameters)
    {
        $parameters = array_merge(
            $this->container->get('request_stack')->getMasterRequest()->query->all(),
            $parameters
        );
        $parameters = array_filter($parameters);
        return '?'.http_build_query($parameters, null, '&');
    }

    public function email($email)
    {
        if (!$email) {
            return '';
        }
        return '<a href="mailto:'.$email.'">'.$email.'</a>';
    }

    public function address($address)
    {
        if (!$address) {
            return '';
        }
        return $address->getStreet().'<br>'.$address->getZip().' '.$address->getCity();
    }

    public function gender($person)
    {
        if (!$person->getGender()) {
            return null;
        }

        return $this->container->get('translator')->trans('label.gender.'.$person->getGender());
    }

    public function genderName($person)
    {
        return trim($this->gender($person).' '.$person);
    }

    public function getName()
    {
        return 'yakamara_extension';
    }
}
