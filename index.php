<?php

require __DIR__.'/laravel/vendor/autoload.php';

$app = require_once __DIR__.'/laravel/bootstrap/app.php';

$app->handleRequest(Illuminate\Http\Request::capture());
