<?php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Models\Application;
use Controllers\WebSiteController;

require_once '../app/init.php';

$app->error(function (\Exception $exception) use ($app) {

    $logger = $app['logger'];

    if ($exception instanceof HttpException && $exception->getStatusCode() != '500') {
        if ($exception->getStatusCode() == '404') {
            $logger->error('Route not found', ['session' => $app->getSessionProvider()->all(), 'request' => $app->getRequest()->getPathInfo()]);
            return $app->getTwig()->render('/404.html.twig');
        } else {
            $logger->error('Error #' . $exception->getStatusCode(), ['url' => $app->getRequest()->getBaseUrl()]);
            return $app->getTwig()->render('/500.html.twig');
        }
    } else {
        $logger->error($exception->getMessage(), ['exception' => $exception]);
        if ($app['config']['debug']) {
            return new Response("Code error {$exception->getCode()} " . $exception->getMessage() . json_encode($exception->getTrace()), ($exception instanceof HttpException) ? $exception->getStatusCode() : 500);
        }
        return $app->getTwig()->render('/500.html.twig');
    }
});


date_default_timezone_set($app['config']['timezone']);
$app->getMysql()->execute("SET GLOBAL time_zone = '" . date('P') . "'");
ini_set('display_errors', $app['config']['debug']);
$error_level = ($app['config']['debug'] ? E_ALL : (E_ALL & ~E_NOTICE));


error_reporting($error_level);
if (!$app['config']['debug']) {
    set_error_handler(function () use ($app) {
        error_log(json_encode(func_get_args() + ['url' => $_SERVER['REQUEST_URI']]));
        $app->abort(500);
    }, E_USER_ERROR);
}

$app->getSessionProvider()->start();

$app->before(function (Request $request, Application $app) {
}, Application::EARLY_EVENT);

$app->after(function (Request $request, Response $response, Application $app) {
});

$app->run();