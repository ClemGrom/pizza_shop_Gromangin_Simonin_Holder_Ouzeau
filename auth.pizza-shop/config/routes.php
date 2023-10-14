<?php
declare(strict_types=1);

use Slim\App;

return function (App $app): void {

    $app->post('/users/signin', $app->getContainer()->get('users.signin'))
        ->setName('signin');
    $app->get('/users/signin', $app->getContainer()->get('users.signin'))
        ->setName('signin');

    $app->get('/users/validate', $app->getContainer()->get('users.validate'))
        ->setName('validite');

    $app->post('/users/refresh', $app->getContainer()->get('users.refresh'))
        ->setName('refresh_token');
};