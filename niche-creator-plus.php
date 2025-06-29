<?php
/**
 * Plugin Name: Niche Creator Plus
 * Plugin URI: https://example.com/plugins/niche-creator-plus
 * Description: Un plugin para WordPress que facilita la creación y gestión de sitios web de nicho.
 * Version: 1.0.1
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
define('NICHE_CREATOR_PLUS_VERSION', '1.0.1');
define('NICHE_CREATOR_PLUS_NAME', 'niche-creator-plus');
define('NICHE_CREATOR_PLUS_PATH', plugin_dir_path(__FILE__));
define('NICHE_CREATOR_PLUS_FILE', __FILE__);
define('NICHE_CREATOR_PLUS_URL', plugin_dir_url(__FILE__));
define('NICHE_CREATOR_PLUS_BASENAME', plugin_basename(__FILE__));

/**
 * Código que se ejecuta durante la activación del plugin.
 */
function activate_niche_creator_plus() {
    // Verificar que el plugin principal Niche Creator esté activo
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    if (!is_plugin_active('niche-creator/niche-creator.php')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('Este plugin requiere que Niche Creator esté instalado y activado. Por favor, activa Niche Creator antes de activar este plugin.');
    }
    
    // Verificar que Action Scheduler esté disponible (debería estar incluido en Niche Creator)
    if (!function_exists('as_schedule_single_action')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('Este plugin requiere Action Scheduler, que debería estar incluido en el plugin Niche Creator. Por favor, contacta al soporte técnico.');
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
