<?php
$db = require __DIR__ . '/db.php';
// test database! Important not to run tests on production or development databases
$db['dsn'] = 'mysql:host=192.168.0.5:3356;dbname=kdv';

return $db;
