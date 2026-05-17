<?php
// Конфигурация подключения к базе данных и общие настройки приложения.
// На боевом хостинге заменить значения на параметры от своего сервера.

return [
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'asiamart',
        'user'     => 'asiamart',
        'password' => 'asiamart_pwd',
        'charset'  => 'utf8mb4',
    ],
    'app' => [
        'name'         => 'AsiaMart',
        'tagline'      => 'Премиальные продукты из Азии',
        'email'        => 'asiamart@example.com',
        'phone'        => '+7 (000) 000-00-00',
        'currency'     => '₽',
        'free_ship'    => 3000,
        'delivery_fee' => 350,
    ],
];
