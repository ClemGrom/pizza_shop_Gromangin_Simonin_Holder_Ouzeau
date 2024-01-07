<?php

namespace pizzashop\config;

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager;
use Slim\Factory\AppFactory;

$actions = require_once __DIR__ . '/actions_dependencies.php';
$settings = require_once __DIR__ . '/settings.php';

$builder = new ContainerBuilder();
$builder->addDefinitions($actions);
$builder->addDefinitions($settings);
$c = $builder->build();
$app = AppFactory::createFromContainer($c);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->setBasePath("/api");
$app->addErrorMiddleware(true, false, false);

return $app;