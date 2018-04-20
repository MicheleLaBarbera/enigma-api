<?php
use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\Collection as MicroCollection;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;

$loader = new Loader();

$loader->registerNamespaces(
    [
        'Model' => __DIR__ . '/models/',
        'Controller' => __DIR__ . '/controllers/',
        'Firebase\JWT' => __DIR__ . '/vendor/firebase/php-jwt/src/'
    ]
);

$loader->register();

$di = new FactoryDefault();
$di->set(
    'db',
    function () {
        return new PdoMysql(
            [
                'host'     => 'localhost',
                'username' => 'root',
                'password' => '1234',
                'dbname'   => 'easy_cmk',
            ]
        );
    }
);

$app = new Micro($di);

$app->before(function() use ($app) {
    $app->response->setHeader("Access-Control-Allow-Origin", '*')
      ->setHeader("Access-Control-Allow-Methods", 'GET,PUT,POST,DELETE,OPTIONS')
      ->setHeader("Access-Control-Allow-Headers", 'Origin, X-Requested-With, Content-Range, Content-Disposition, Content-Type, Authorization')
      ->setHeader("Access-Control-Allow-Credentials", true);

    $app->response->sendHeaders();
    return true;
});

$app->options('/{catch:(.*)}', function() use ($app) {
    $app->response->setStatusCode(200, "OK")->send();
    return true;
});

$users = new MicroCollection();
$users->setHandler(new Controller\Users());
$users->setPrefix('/users');
$users->post('/auth', 'auth');
$app->mount($users);

$infrastructures = new MicroCollection();
$infrastructures->setHandler(new Controller\Infrastructures());
$infrastructures->setPrefix('/infrastructures');
$infrastructures->get('/get', 'get');
$app->mount($infrastructures);

$hosts = new MicroCollection();
$hosts->setHandler(new Controller\Hosts());
$hosts->setPrefix('/hosts');
$hosts->post('/get', 'get');
$app->mount($hosts);

$services = new MicroCollection();
$services->setHandler(new Controller\Services());
$services->setPrefix('/services');
$services->post('/get', 'get');
$app->mount($services);

$app->handle();
?>
