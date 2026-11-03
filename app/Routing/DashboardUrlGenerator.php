<?php

namespace App\Routing;

use App\Support\UrlId;
use Illuminate\Routing\UrlGenerator;

class DashboardUrlGenerator extends UrlGenerator
{
    public function route($name, $parameters = [], $absolute = true)
    {
        $routeName = $name instanceof \BackedEnum ? $name->value : $name;

        if (is_string($routeName) && UrlId::shouldHide($routeName)) {
            $parameters = UrlId::encodeParameters($parameters);
        }

        return parent::route($name, $parameters, $absolute);
    }
}
