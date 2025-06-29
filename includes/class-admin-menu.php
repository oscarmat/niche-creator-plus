<?php
/**
 * La clase para gestionar el menú de administración
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Niche_Creator_Plus
 * @subpackage Niche_Creator_Plus/includes
 */

/**
 * La clase para gestionar el menú de administración.
 *
 * Esta clase contiene la lógica para añadir el menú de administración
 * y los submenús necesarios para el plugin.
 *
 * @since      1.0.0
 * @package    Niche_Creator_Plus
 * @subpackage Niche_Creator_Plus/includes
 * @author     Niche Creator Team
 */
class Niche_Creator_Plus_Admin_Menu {

    /**
     * El ID de este plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    El ID de este plugin.
     */
    private $plugin_name;

    /**
     * La versión de este plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    La versión actual de este plugin.
     */
    private $version;

    /**
     * Inicializa la clase y establece sus propiedades.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       El nombre de este plugin.
     * @param      string    $version    La versión de este plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // Registrar el menú de administración
        add_action('admin_menu', array($this, 'register_admin_menu'), 99);
    }

    /**
     * Registra el menú de administración y los submenús
     *
     * @since    1.0.0
     */
    public function register_admin_menu() {
        global $menu;

        $slug_padre = 'nichecreator_creator';
        $posicion_base = null;

        foreach ( $menu as $pos => $datos ) {
            if ( isset( $datos[2] ) && $datos[2] === $slug_padre ) {
                $posicion_base = $pos + 0.1;
                break;
            }
        }

        if ( $posicion_base !== null ) {
            // Añadir menú principal y submenú
            add_menu_page(
                'Niche Creator Plus',
                'Niche Creator Plus',
                'manage_options',
                'nichecreator_plus',
                array($this, 'render_main_page'),
                'dashicons-update-alt',
                $posicion_base
            );

            add_submenu_page(
                'nichecreator_plus',
                'Reprocesar eventos fallidos',
                'Reprocesar eventos',
                'manage_options',
                'nichecreator_plus_reprocesar',
                array($this, 'render_main_page')
            );
            
            // Añadir página de diagnóstico
            add_submenu_page(
                'nichecreator_plus',
                'Diagnóstico',
                'Diagnóstico',
                'manage_options',
                'nichecreator_plus_diagnostico',
                array($this, 'render_diagnostico_page')
            );
        } else {
            // Mostrar aviso si no está activo el menú padre
            add_action('admin_notices', array($this, 'display_dependency_notice'));
        }
    }
    
    /**
     * Muestra un aviso si el plugin Niche Creator no está activo
     *
     * @since    1.0.0
     */
    public function display_dependency_notice() {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>Niche Creator Plus:</strong> No se ha podido cargar porque el plugin principal <code>Niche Creator</code> no está activo.</p>';
        echo '</div>';
    }
    
    /**
     * Renderiza la página principal del plugin
     *
     * @since    1.0.0
     */
    public function render_main_page() {
        // Instanciar el reprocesador y llamar a su método de renderizado
        $reprocessor = new Niche_Creator_Plus_Reprocessor($this->plugin_name, $this->version);
        $reprocessor->render_page();
    }
    
    /**
     * Renderiza la página de diagnóstico del plugin
     *
     * @since    1.0.2
     */
    public function render_diagnostico_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/niche-creator-plus-diagnostico.php';
    }
}

// Función de compatibilidad para mantener el código existente funcionando
function nichecreator_plus_render_reprocesar() {
    $reprocessor = new Niche_Creator_Plus_Reprocessor('niche-creator-plus', NICHE_CREATOR_PLUS_VERSION);
    $reprocessor->render_page();
}

// Función de compatibilidad para la página principal
function nichecreator_plus_render_page() {
    nichecreator_plus_render_reprocesar();
}
