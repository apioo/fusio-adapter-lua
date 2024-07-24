<?php

use Fusio\Adapter\Lua\Action\WorkerLuaLocal;
use Fusio\Engine\Adapter\ServiceBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $services = ServiceBuilder::build($container);
    $services->set(WorkerLuaLocal::class);
};
