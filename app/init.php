<?php

use Models\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/utils.php';

define("ROOT", realpath(__DIR__ . '/..'));
$request = Request::createFromGlobals();
$cookieDomain = $request->getHost();
if ($cookieDomain && strpos($cookieDomain, '.') && !intval(strtok($cookieDomain, '.'))) {
    $cookieDomain = '.' . $cookieDomain;
}

$app = new Application();

$loader = new \Symfony\Component\Routing\Loader\YamlFileLoader(new \Symfony\Component\Config\FileLocator(ROOT . '/config'));
$collection = $loader->load('routes.yml');
$app['routes']->addCollection($collection);

$app->register(new \Providers\Config\ConfigProvider());
$app->register(new \Providers\Time\TimeProvider());
$app->register(new \Providers\Curl\CurlProvider());
$app->register(new \Providers\Shell\ShellProvider());
$app->register(new \Providers\File\FileProvider());
$app->register(new \Providers\Logger\LoggerProvider());
$app->register(new \Providers\Mysql\PDOProvider());
$app->register(new \Providers\Mail\MailProvider());
$app->register(new \Providers\ObjectCache\ObjectCacheProvider());
$app->register(new \Silex\Provider\SessionServiceProvider(), [
    'session.storage.options' => [
        'cookie_lifetime' => 0,
        'cookie_path' => '/',
        'cookie_domain' => $cookieDomain
    ]
]);
$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());


$app->register(new \Silex\Provider\TwigServiceProvider(), [
    'twig.path' => ROOT . '/views',
    'twig.options' => [
        'cache' => ROOT . '/cache',
        'debug' => $app['config']['debug']
    ]
]);



?>