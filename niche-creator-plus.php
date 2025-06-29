<?php
/**
 * Plugin Name: Niche Creator Plus
 * Plugin URI: https://example.com/plugins/niche-creator-plus
 * Description: Un plugin para WordPress que facilita la creación y gestión de sitios web de nicho.
 * Version: 1.0.0
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

// Definir constantes del plugin
define('NICHE_CREATOR_PLUS_VERSION', '1.0.0');
define('NICHE_CREATOR_PLUS_PATH', plugin_dir_path(__FILE__));
define('NICHE_CREATOR_PLUS_URL', plugin_dir_url(__FILE__));
define('NICHE_CREATOR_PLUS_BASENAME', plugin_basename(__FILE__));

/**
 * Código que se ejecuta durante la activación del plugin.
 */
function activate_niche_creator_plus() {
    // Acciones a realizar durante la activación
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
