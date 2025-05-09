<?php

namespace delahaye\googlemaps;

use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class Helpers
{
    public static function checkMode(): string
    {
        if (System::getContainer()->get('contao.routing.scope_matcher')
        ->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))
        ) {
            return "BE";
        } else {
            return "FE";
        }
    }
    
}