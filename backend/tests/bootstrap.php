<?php

/**
 * Bootstrap file pour PHPUnit
 * Configure l'environnement de test avant l'exécution des tests
 */

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Charger les variables d'environnement de test
if (file_exists(dirname(__DIR__).'/.env.test')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env.test');
}

// S'assurer que l'environnement est configuré en test
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';

// Nettoyer le cache de test au démarrage
$cacheDir = dirname(__DIR__).'/var/cache/test';
if (is_dir($cacheDir)) {
    // Fonction récursive pour supprimer un dossier
    function removeDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
    
    removeDirectory($cacheDir);
}

// Configurer le timezone pour éviter les warnings
date_default_timezone_set('UTC');
