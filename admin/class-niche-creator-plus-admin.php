<?php
/**
 * La funcionalidad específica de administración del plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Niche_Creator_Plus
 * @subpackage Niche_Creator_Plus/admin
 */

/**
 * La clase de funcionalidad específica de administración del plugin.
 *
 * Define el nombre del plugin, la versión y dos ejemplos de hooks para
 * enqueue el estilo específico de administración y JavaScript.
 *
 * @package    Niche_Creator_Plus
 * @subpackage Niche_Creator_Plus/admin
 * @author     Niche Creator Team
 */
class Niche_Creator_Plus_Admin {

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

    }

    /**
     * Registra los estilos para el área de administración.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * Este método es un gancho para registrar los estilos de la página de administración.
         *
         * WordPress proporciona una función wp_enqueue_style() para registrar los estilos
         * que se cargarán en el área de administración.
         *
         * Una instancia de esta clase debe pasar a la función cuando se especifica este
         * método como un callback de un hook particular:
         *
         * wp_enqueue_style( 'plugin-name', plugin_dir_url( __FILE__ ) . 'css/plugin-name-admin.css', array(), $this->version, 'all' );
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/niche-creator-plus-admin.css', array(), $this->version, 'all');

    }

    /**
     * Registra el JavaScript para el área de administración.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * Este método es un gancho para registrar el JavaScript de la página de administración.
         *
         * WordPress proporciona una función wp_enqueue_script() para registrar el script
         * que se cargará en el área de administración.
         *
         * Una instancia de esta clase debe pasar a la función cuando se especifica este
         * método como un callback de un hook particular:
         *
         * wp_enqueue_script( 'plugin-name', plugin_dir_url( __FILE__ ) . 'js/plugin-name-admin.js', array( 'jquery' ), $this->version, false );
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/niche-creator-plus-admin.js', array('jquery'), $this->version, false);

    }

    /**
     * Añade opciones de menú para el plugin en el área de administración.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        // Instanciar la clase de menú de administración
        $admin_menu = new Niche_Creator_Plus_Admin_Menu($this->plugin_name, $this->version);
        
        // El menú se registra automáticamente en el constructor de la clase
        // No necesitamos hacer nada más aquí
    }

    /**
     * Renderiza la página del dashboard de administración del plugin
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_dashboard() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/niche-creator-plus-admin-display.php';
    }

    /**
     * Renderiza la página de configuración del plugin
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_settings() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/niche-creator-plus-admin-settings.php';
    }

}
