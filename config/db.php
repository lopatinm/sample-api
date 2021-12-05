<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=sample',
    'username' => 'phpmyadmin',
    'password' => 'vfhfnfy87',
    'charset' => 'utf8',
    'on afterOpen' => function($event) {
        $event->sender->createCommand("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';")->execute();
    },
];
