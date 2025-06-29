<?php
/**
 * Archivo de depuración para identificar errores de activación
 */

// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definir constantes de WordPress si no están definidas
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

echo "<h1>Diagnóstico de Niche Creator Plus</h1>";

// Verificar versión de PHP
echo "<h2>Información del sistema</h2>";
echo "<p>Versión de PHP: " . phpversion() . "</p>";

// Verificar si Action Scheduler está disponible
echo "<h2>Verificación de dependencias</h2>";
if (function_exists('as_schedule_single_action')) {
    echo "✓ Action Scheduler está disponible<br>";
} else {
    echo "❌ <strong>ERROR:</strong> Action Scheduler no está disponible. Este plugin requiere WooCommerce o el plugin Action Scheduler.<br>";
    echo "<p>Este es probablemente el origen del error de activación. El plugin intenta usar funciones de Action Scheduler que no están disponibles.</p>";
}

// Verificar si el plugin principal Niche Creator está activo
if (function_exists('is_plugin_active')) {
    if (is_plugin_active('niche-creator/niche-creator.php')) {
        echo "✓ Plugin Niche Creator está activo<br>";
    } else {
        echo "⚠️ Plugin Niche Creator no está activo. Este plugin complementa al plugin principal.<br>";
    }
} else {
    echo "⚠️ No se puede verificar si Niche Creator está activo (función is_plugin_active no disponible)<br>";
}

// Verificar tablas de Action Scheduler
echo "<h3>Verificación de tablas de Action Scheduler</h3>";
global $wpdb;
if (isset($wpdb)) {
    $tablas = [
        $wpdb->prefix . 'actionscheduler_actions',
        $wpdb->prefix . 'actionscheduler_groups',
        $wpdb->prefix . 'actionscheduler_logs'
    ];
    
    foreach ($tablas as $tabla) {
        $existe = $wpdb->get_var("SHOW TABLES LIKE '$tabla'");
        if ($existe) {
            echo "✓ Tabla $tabla existe<br>";
        } else {
            echo "❌ Tabla $tabla no existe<br>";
        }
    }
} else {
    echo "⚠️ No se puede acceder a la base de datos de WordPress<br>";
}

// Intentar cargar las clases principales para ver si hay errores
try {
    require_once 'includes/class-niche-creator-plus-loader.php';
    echo "✓ class-niche-creator-plus-loader.php cargado correctamente<br>";
    
    require_once 'includes/class-niche-creator-plus-i18n.php';
    echo "✓ class-niche-creator-plus-i18n.php cargado correctamente<br>";
    
    require_once 'includes/class-admin-menu.php';
    echo "✓ class-admin-menu.php cargado correctamente<br>";
    
    require_once 'includes/class-reprocessor.php';
    echo "✓ class-reprocessor.php cargado correctamente<br>";
    
    require_once 'includes/class-niche-creator-plus-updater.php';
    echo "✓ class-niche-creator-plus-updater.php cargado correctamente<br>";
    
    require_once 'admin/class-niche-creator-plus-admin.php';
    echo "✓ class-niche-creator-plus-admin.php cargado correctamente<br>";
    
    require_once 'public/class-niche-creator-plus-public.php';
    echo "✓ class-niche-creator-plus-public.php cargado correctamente<br>";
    
    require_once 'includes/functions.php';
    echo "✓ functions.php cargado correctamente<br>";
    
    // Intentar instanciar las clases principales
    echo "<h3>Intentando instanciar clases...</h3>";
    
    $loader = new Niche_Creator_Plus_Loader();
    echo "✓ Niche_Creator_Plus_Loader instanciado correctamente<br>";
    
    $i18n = new Niche_Creator_Plus_i18n();
    echo "✓ Niche_Creator_Plus_i18n instanciado correctamente<br>";
    
    $admin_menu = new Niche_Creator_Plus_Admin_Menu('niche-creator-plus', '1.0.0');
    echo "✓ Niche_Creator_Plus_Admin_Menu instanciado correctamente<br>";
    
    $reprocessor = new Niche_Creator_Plus_Reprocessor('niche-creator-plus', '1.0.0');
    echo "✓ Niche_Creator_Plus_Reprocessor instanciado correctamente<br>";
    
    // No instanciamos el updater aquí porque requiere constantes de WordPress
    
    $admin = new Niche_Creator_Plus_Admin('niche-creator-plus', '1.0.0');
    echo "✓ Niche_Creator_Plus_Admin instanciado correctamente<br>";
    
    $public = new Niche_Creator_Plus_Public('niche-creator-plus', '1.0.0');
    echo "✓ Niche_Creator_Plus_Public instanciado correctamente<br>";
    
} catch (Error $e) {
    echo "<h2>Error detectado:</h2>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Traza:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Exception $e) {
    echo "<h2>Excepción detectada:</h2>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Traza:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
