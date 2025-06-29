<?php
/**
 * La funcionalidad específica de la parte pública del plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Niche_Creator_Plus
 * @subpackage Niche_Creator_Plus/public
 */

/**
 * La clase de funcionalidad específica de la parte pública del plugin.
 *
 * Define el nombre del plugin, la versión y dos ejemplos de hooks para
 * enqueue el estilo específico del lado público y JavaScript.
 *
 * @package    Niche_Creator_Plus
 * @subpackage Niche_Creator_Plus/public
 * @author     Niche Creator Team
 */
class Niche_Creator_Plus_Public {

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
     * Registra los estilos para el lado público.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * Este método es un gancho para registrar los estilos del lado público.
         *
         * WordPress proporciona una función wp_enqueue_style() para registrar los estilos
         * que se cargarán en el lado público.
         *
         * Una instancia de esta clase debe pasar a la función cuando se especifica este
         * método como un callback de un hook particular:
         *
         * wp_enqueue_style( 'plugin-name', plugin_dir_url( __FILE__ ) . 'css/plugin-name-public.css', array(), $this->version, 'all' );
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/niche-creator-plus-public.css', array(), $this->version, 'all');

    }

    /**
     * Registra el JavaScript para el lado público.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * Este método es un gancho para registrar el JavaScript del lado público.
         *
         * WordPress proporciona una función wp_enqueue_script() para registrar el script
         * que se cargará en el lado público.
         *
         * Una instancia de esta clase debe pasar a la función cuando se especifica este
         * método como un callback de un hook particular:
         *
         * wp_enqueue_script( 'plugin-name', plugin_dir_url( __FILE__ ) . 'js/plugin-name-public.js', array( 'jquery' ), $this->version, false );
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/niche-creator-plus-public.js', array('jquery'), $this->version, false);

    }

}
