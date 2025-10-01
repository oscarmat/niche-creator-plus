<?php
/**
 * La clase principal del plugin Niche Creator Plus
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Niche_Creator_Plus
 */

/**
 * La clase principal del plugin.
 *
 * Esta es la clase que orquesta todo el plugin y define
 * los hooks de admin y del sitio público.
 *
 * @since      1.0.0
 * @package    Niche_Creator_Plus
 * @author     Niche Creator Team
 */
class Niche_Creator_Plus {

    /**
     * El cargador que es responsable de mantener y registrar todos los hooks del plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Niche_Creator_Plus_Loader    $loader    Mantiene y registra todos los hooks para el plugin.
     */
    protected $loader;

    /**
     * El identificador único de este plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    El nombre o identificador único de este plugin.
     */
    protected $plugin_name;

    /**
     * La versión actual del plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    La versión actual del plugin.
     */
    protected $version;

    /**
     * Instancia única del menú de administración.
     *
     * @since    1.0.13
     * @access   private
     * @var      Niche_Creator_Plus_Admin_Menu    $admin_menu    Instancia del menú.
     */
    private $admin_menu;

    /**
     * Instancia única del reprocessor.
     *
     * @since    1.0.13
     * @access   private
     * @var      Niche_Creator_Plus_Reprocessor    $reprocessor    Instancia del reprocessor.
     */
    private $reprocessor;

    /**
     * Define las funcionalidades principales del plugin.
     *
     * Establece el nombre y la versión del plugin que se puede utilizar en todo el plugin.
     * Carga las dependencias, define la configuración regional y establece los hooks para
     * el área de administración y el lado público del sitio.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->plugin_name = 'niche-creator-plus';
        $this->version = NICHE_CREATOR_PLUS_VERSION;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Carga las dependencias requeridas para este plugin.
     *
     * Incluye los siguientes archivos que componen el plugin:
     *
     * - Niche_Creator_Plus_Loader. Orquesta los hooks del plugin.
     * - Niche_Creator_Plus_i18n. Define la funcionalidad de internacionalización.
     * - Niche_Creator_Plus_Admin. Define todos los hooks del área de administración.
     * - Niche_Creator_Plus_Public. Define todos los hooks del lado público.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * La clase responsable de orquestar las acciones y filtros del
         * núcleo del plugin.
         */
        require_once NICHE_CREATOR_PLUS_PATH . 'includes/class-niche-creator-plus-loader.php';

        /**
         * La clase responsable de definir la funcionalidad de internacionalización
         * del plugin.
         */
        require_once NICHE_CREATOR_PLUS_PATH . 'includes/class-niche-creator-plus-i18n.php';

        /**
         * La clase responsable de definir todas las acciones en el área de administración.
         */
        require_once NICHE_CREATOR_PLUS_PATH . 'admin/class-niche-creator-plus-admin.php';

        /**
         * La clase responsable de definir todas las acciones en el lado público.
         */
        require_once NICHE_CREATOR_PLUS_PATH . 'public/class-niche-creator-plus-public.php';
        
        /**
         * La clase responsable de gestionar el menú de administración.
         */
        require_once NICHE_CREATOR_PLUS_PATH . 'includes/class-admin-menu.php';
        
        /**
         * La clase responsable de reprocesar acciones fallidas.
         */
        require_once NICHE_CREATOR_PLUS_PATH . 'includes/class-reprocessor.php';
        
        /**
         * La clase responsable de gestionar las actualizaciones desde GitHub.
         */
        require_once NICHE_CREATOR_PLUS_PATH . 'includes/class-niche-creator-plus-updater.php';
        
        /**
         * Funciones de compatibilidad para mantener el código existente.
         */
        // require_once NICHE_CREATOR_PLUS_PATH . 'includes/functions.php';

        $this->loader = new Niche_Creator_Plus_Loader();
    }

    /**
     * Define la configuración regional del plugin para la internacionalización.
     *
     * Utiliza la clase Niche_Creator_Plus_i18n para establecer el dominio y registrar el hook
     * con WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new Niche_Creator_Plus_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Registra todos los hooks relacionados con la funcionalidad del área de administración
     * del plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Niche_Creator_Plus_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // Guardar/Actualizar opciones del plugin
        $this->loader->add_action('admin_init', $plugin_admin, 'options_update');
        
        // Crear instancia única del reprocessor
        $this->reprocessor = new Niche_Creator_Plus_Reprocessor($this->get_plugin_name(), $this->get_version());
        
        // Crear instancia única del menú de administración
        $this->admin_menu = new Niche_Creator_Plus_Admin_Menu($this->get_plugin_name(), $this->get_version());
        
        // Inyectar el reprocessor en el menú para evitar duplicaciones
        $this->admin_menu->set_reprocessor($this->reprocessor);
        
        // Registrar el menú de administración
        $this->loader->add_action('admin_menu', $this->admin_menu, 'register_admin_menu', 99);
        
        // Inicializar el sistema de actualizaciones desde GitHub
        // No necesitamos registrar hooks adicionales ya que el updater
        // registra sus propios filtros en su constructor
    }

    /**
     * Registra todos los hooks relacionados con la funcionalidad del lado público
     * del plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new Niche_Creator_Plus_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    /**
     * Ejecuta el cargador para ejecutar todos los hooks con WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        // Inicializar el sistema de actualizaciones desde GitHub
        $updater = new Niche_Creator_Plus_Updater(
            $this->plugin_name,
            $this->version,
            NICHE_CREATOR_PLUS_FILE,
            'oscarmat',
            'niche-creator-plus'
        );
        
        // Registrar los hooks del updater
        $updater->init();
        
        $this->loader->run();
    }

    /**
     * El nombre del plugin utilizado para identificarlo dentro de WordPress.
     *
     * @since     1.0.0
     * @return    string    El nombre del plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * La referencia a la clase que orquesta los hooks del plugin.
     *
     * @since     1.0.0
     * @return    Niche_Creator_Plus_Loader    Orquesta los hooks del plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Recupera el número de versión del plugin.
     *
     * @since     1.0.0
     * @return    string    El número de versión del plugin.
     */
    public function get_version() {
        return $this->version;
    }

}
