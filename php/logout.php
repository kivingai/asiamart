<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

logout_user();
redirect('/index.php');
