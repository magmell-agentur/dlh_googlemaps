<?php

namespace delahaye\googlemaps;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class DlhGoogleMapsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }



}
