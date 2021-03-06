<?php
use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\Collection as MicroCollection;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;

error_reporting(E_ERROR | E_PARSE);

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
$users->post('/create', 'create');
$users->get('/get', 'get');
$app->mount($users);

$hostgroups = new MicroCollection();
$hostgroups->setHandler(new Controller\Hostgroups());
$hostgroups->setPrefix('/hostgroups');
$hostgroups->post('/get', 'get');
$hostgroups->post('/get/{id:[0-9]+}', 'getID');
$hostgroups->put('/set/{id:[0-9]+}', 'setDefaultGroup');
$hostgroups->post('/create', 'create');
$hostgroups->get('/getUser/{id:[0-9]+}', 'getUser');
$app->mount($hostgroups);

$hosts = new MicroCollection();
$hosts->setHandler(new Controller\Hosts());
$hosts->setPrefix('/hosts');
$hosts->post('/get', 'get');
$hosts->post('/state', 'getByState');
$app->mount($hosts);

$services = new MicroCollection();
$services->setHandler(new Controller\Services());
$services->setPrefix('/services');
$services->post('/get', 'get');
$app->mount($services);

$customers = new MicroCollection();
$customers->setHandler(new Controller\Customers());
$customers->setPrefix('/customers');
$customers->get('/get', 'get');
$customers->get('/get/{id:[0-9]+}', 'getID');
$customers->post('/create', 'create');
$app->mount($customers);

$app->handle();
?>
