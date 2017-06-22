<?php

use Lalbert\Silex\Provider\MongoDBServiceProvider;
use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\FormServiceProvider;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new HttpFragmentServiceProvider());

$app->register(
    new Silex\Provider\TranslationServiceProvider(), [
    'locale' => 'en'
    ]
);

$db = parse_ini_file(__DIR__.'/../config/db.ini');
if (!isset($db['host']) && !isset($db['port'])) {
    return false;
}
$app->register(
    new MongoDBServiceProvider(), [
    'mongodb.config' => [
        'server' => "mongodb://".$db['host'].":".$db['port'],
        'options' => [],
        'driverOptions' => [],
    ]
    ]
);

$app['twig'] = $app->extend(
    'twig', function ($twig, $app) {
        // add custom globals, filters, tags, ...

        return $twig;
    }
);

return $app;
