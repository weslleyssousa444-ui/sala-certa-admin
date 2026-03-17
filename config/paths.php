<?php

// BASE_PATH agora aponta corretamente para a raiz do projeto
define('BASE_PATH', dirname(__DIR__));

// Caminhos das pastas do projeto
define('ASSETS_PATH', BASE_PATH . '/assets');
define('CLASSES_PATH', BASE_PATH . '/classes');
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('PAGES_PATH', BASE_PATH . '/pages');
define('UTILS_PATH', BASE_PATH . '/utils');
define('UPLOADS_PATH', BASE_PATH . '/uploads');

// URLs para assets
define('ASSETS_URL', APP_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMG_URL', ASSETS_URL . '/img');
define('UPLOADS_URL', APP_URL . '/uploads');