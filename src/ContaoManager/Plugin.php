<?php

namespace delahaye\googlemaps\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use delahaye\googlemaps\DlhGoogleMapsBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(DlhGoogleMapsBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
        ];
    }
}
