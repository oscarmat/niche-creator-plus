<?php
/**
 * Plugin Name: Niche Creator Plus
 * Plugin URI: https://example.com/plugins/niche-creator-plus
 * Description: Un plugin para WordPress que facilita la creación y gestión de sitios web de nicho.
 * Version: 1.0.17
 * Author: Niche Creator Team
 * Author URI: https://example.com
 * Text Domain: niche-creator-plus
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Si este archivo es llamado directamente, abortar.
if (!defined('WPINC')) {
    die;
}

/**
 * Definir constantes del plugin
 */
define('NICHE_CREATOR_PLUS_VERSION', '1.0.17');
define('NICHE_CREATOR_PLUS_NAME', 'niche-creator-plus');
define('NICHE_CREATOR_PLUS_PATH', plugin_dir_path(__FILE__));
define('NICHE_CREATOR_PLUS_FILE', __FILE__);
define('NICHE_CREATOR_PLUS_URL', plugin_dir_url(__FILE__));
define('NICHE_CREATOR_PLUS_BASENAME', plugin_basename(__FILE__));

/**
 * Código que se ejecuta durante la activación del plugin.
 */
function activate_niche_creator_plus() {
    error_log('Niche Creator Plus: Iniciando activación del plugin');
    
    try {
        // Verificar que el plugin principal Niche Creator esté activo
        error_log('Niche Creator Plus: Verificando si is_plugin_active está disponible');
        if (!function_exists('is_plugin_active')) {
            error_log('Niche Creator Plus: Cargando plugin.php para obtener is_plugin_active');
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        error_log('Niche Creator Plus: Verificando si el plugin principal está activo');
        
        // Listar todos los plugins activos para diagnóstico
        $active_plugins = get_option('active_plugins');
        error_log('Niche Creator Plus: Plugins activos: ' . print_r($active_plugins, true));
        
        // Ruta correcta del plugin principal (confirmada por el usuario)
        $plugin_main_path = 'nichecreator/nichecreator.php';
        
        // También comprobar algunas variantes por si acaso
        $plugin_variants = [
            $plugin_main_path,
            'niche-creator/niche-creator.php',
            'nichecreator/index.php'
        ];
        
        $plugin_found = false;
        foreach ($plugin_variants as $plugin_path) {
            if (is_plugin_active($plugin_path)) {
                error_log('Niche Creator Plus: Plugin principal encontrado como: ' . $plugin_path);
                $plugin_found = true;
                break;
            }
        }
        
        if (!$plugin_found) {
            error_log('Niche Creator Plus: ERROR - Plugin principal Niche Creator no está activo');
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die('Este plugin requiere que Niche Creator esté instalado y activado. Por favor, activa Niche Creator antes de activar este plugin.');
        }
        error_log('Niche Creator Plus: Plugin principal Niche Creator está activo');
        
        // Verificar que Action Scheduler esté disponible (debería estar incluido en Niche Creator)
        error_log('Niche Creator Plus: Verificando si Action Scheduler está disponible');
        if (!function_exists('as_schedule_single_action')) {
            error_log('Niche Creator Plus: ERROR - Action Scheduler no está disponible');
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die('Este plugin requiere Action Scheduler, que debería estar incluido en el plugin Niche Creator. Por favor, contacta al soporte técnico.');
        }
        error_log('Niche Creator Plus: Action Scheduler está disponible');
        
        // Verificar que las clases necesarias existan
        error_log('Niche Creator Plus: Verificando archivos de clases necesarias');
        $required_files = [
            'includes/class-niche-creator-plus.php',
            'includes/class-niche-creator-plus-loader.php',
            'includes/class-admin-menu.php',
            'includes/class-reprocessor.php'
        ];
        
        foreach ($required_files as $file) {
            $full_path = plugin_dir_path(__FILE__) . $file;
            if (!file_exists($full_path)) {
                error_log('Niche Creator Plus: ERROR - Archivo requerido no existe: ' . $full_path);
                deactivate_plugins(plugin_basename(__FILE__));
                wp_die('Error al activar Niche Creator Plus: Archivo requerido no existe: ' . $file);
            }
            error_log('Niche Creator Plus: Archivo existe: ' . $file);
        }
        
        error_log('Niche Creator Plus: Activación completada exitosamente');
    } catch (Exception $e) {
        error_log('Niche Creator Plus: ERROR durante la activación - ' . $e->getMessage());
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('Error al activar Niche Creator Plus: ' . $e->getMessage());
    }
}

/**
 * Código que se ejecuta durante la desactivación del plugin.
 */
function deactivate_niche_creator_plus() {
    // Acciones a realizar durante la desactivación
}

register_activation_hook(__FILE__, 'activate_niche_creator_plus');
register_deactivation_hook(__FILE__, 'deactivate_niche_creator_plus');

/**
 * Incluir las clases necesarias para el funcionamiento del plugin
 */
require_once NICHE_CREATOR_PLUS_PATH . 'includes/class-niche-creator-plus.php';

/**
 * Comienza la ejecución del plugin.
 */
function run_niche_creator_plus() {
    $plugin = new Niche_Creator_Plus();
    $plugin->run();
}

run_niche_creator_plus();
