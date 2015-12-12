<?php
require_once __DIR__.'/../vendor/autoload.php';

use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use DerAlex\Silex\YamlConfigServiceProvider;

$app = new Silex\Application();

/** Configuration */
$app->register(new YamlConfigServiceProvider(__DIR__.'/../config/app.yml'));

/**  Application */
$app->register(new DoctrineServiceProvider(), array(
    'db.options' => $app['config']['database']
));
$app->register(new MonologServiceProvider(), array(
    'monolog.logfile'    => __DIR__ . '/../log/app.log',
    'monolog.name'       => 'pamplemousse',
    'monolog.level'      => 100
));
$app->register(new SessionServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app->register(new UrlGeneratorServiceProvider());

/** Services */
$app['photos'] = $app->share(function ($app) {
    return new Pamplemousse\Photos\Service($app['config'], $app['db']);
});
$app['slug'] = $app->share(function ($app) {
    return new Cocur\Slugify\Slugify();
});

/** Controller */
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get('/', Pamplemousse\Controller::class.'::indexAction');
$app->mount('/photos/', new Pamplemousse\Photos\Router());
$app->mount('/admin/', new Pamplemousse\Admin\Router());

return $app;
