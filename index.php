<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");

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


$app->handle();
?>