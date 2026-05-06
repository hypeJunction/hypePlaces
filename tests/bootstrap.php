<?php
/**
 * PHPUnit bootstrap for hypePlaces plugin tests.
 * Plugin must be installed at {elgg_root}/mod/hypePlaces/
 */

// tests/ -> mod/plugin/ -> mod/ -> elgg_root/
$elggRoot = dirname(dirname(dirname(__DIR__)));

require_once $elggRoot . '/vendor/autoload.php';

// Load Elgg test classes (UnitTestCase, IntegrationTestCase, etc.)
$testClassesDir = $elggRoot . '/vendor/elgg/elgg/engine/tests/classes';
spl_autoload_register(function ($class) use ($testClassesDir) {
    $file = $testClassesDir . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load plugin lib files so classes + functions are available (namespaced helpers, Place class)
$pluginRoot = dirname(__DIR__);
if (file_exists($pluginRoot . '/elgg-plugin.php')) {
    require_once $pluginRoot . '/classes/hypeJunction/Places/Place.php';
    if (file_exists($pluginRoot . '/lib/functions.php')) {
        require_once $pluginRoot . '/lib/functions.php';
    }
    if (file_exists($pluginRoot . '/lib/hooks.php')) {
        require_once $pluginRoot . '/lib/hooks.php';
    }
}

\Elgg\Application::loadCore();
