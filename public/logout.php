<?php
require_once __DIR__ . '/../includes/helpers.php';
unset($_SESSION['user_id']);
session_regenerate_id(true);
flash_set('Вы вышли из аккаунта.', 'success');
header('Location: /');
