<?php
namespace app\commands;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';


use Workerman\Worker;
use yii\console;
use Yii;

$users = [];

$context = array(
    'ssl' => array(
        'local_cert'  => '/etc/ssl/wetime.ru/wetime.ru.crt',
        'local_pk'    => '/etc/ssl/wetime.ru/wetime.ru.key',
        'verify_peer' => false,
    )
);

$ws_worker = new Worker("websocket://wetime.ru:8000", $context);

$ws_worker->transport = 'ssl';
$ws_worker->name = 'wetime';

$ws_worker->onWorkerStart = function() use (&$users){
    $inner_tcp_worker = new Worker("tcp://127.0.0.1:1234");
    $inner_tcp_worker->onMessage = function($connection, $dt) use (&$users) {
        $data = json_decode($dt);
    };
    $inner_tcp_worker->listen();
};

$ws_worker->onMessage = function($connection, $data){
    $connection->send('hello ' . $data);
};

$ws_worker->onConnect = function($connection) use (&$users){
    $connection->onWebSocketConnect = function($connection) use (&$users){
        $sessionId = md5(time()."-".rand());
        $users[$sessionId] = $connection;
        $data = array();
        $data['action'] = 'setSessionId';
        $data['sessionId'] = $sessionId;
        $connection->send(json_encode($data));
    };
};

$ws_worker->onClose = function($connection) use(&$users){
    $user = array_search($connection, $users);
    unset($users[$user]);
};

Worker::runAll();