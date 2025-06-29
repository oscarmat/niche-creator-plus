<?php
/**
 * Clase para gestionar las actualizaciones del plugin desde GitHub
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Niche_Creator_Plus
 * @subpackage Niche_Creator_Plus/includes
 */

/**
 * Clase para gestionar las actualizaciones del plugin desde GitHub.
 *
 * Esta clase se encarga de comprobar si hay actualizaciones disponibles
 * en GitHub y de gestionar el proceso de actualización.
 *
 * @since      1.0.0
 * @package    Niche_Creator_Plus
 * @subpackage Niche_Creator_Plus/includes
 * @author     Niche Creator Team
 */
class Niche_Creator_Plus_Updater {

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
     * El archivo principal del plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_file    La ruta al archivo principal del plugin.
     */
    private $plugin_file;

    /**
     * El slug del plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_slug    El slug del plugin.
     */
    private $plugin_slug;

    /**
     * El usuario de GitHub.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $github_username    El usuario de GitHub.
     */
    private $github_username;

    /**
     * El repositorio de GitHub.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $github_repo    El repositorio de GitHub.
     */
    private $github_repo;

    /**
     * Inicializa la clase y establece sus propiedades.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       El nombre de este plugin.
     * @param    string    $version           La versión de este plugin.
     * @param    string    $plugin_file       La ruta al archivo principal del plugin.
     * @param    string    $github_username   El usuario de GitHub.
     * @param    string    $github_repo       El repositorio de GitHub.
     */
    public function __construct($plugin_name, $version, $plugin_file, $github_username, $github_repo) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = basename(dirname($plugin_file));
        $this->github_username = $github_username;
        $this->github_repo = $github_repo;

        // Añadir filtros para el proceso de actualización
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'post_install'), 10, 3);
    }

    /**
     * Comprueba si hay actualizaciones disponibles.
     *
     * @since    1.0.0
     * @param    object    $transient    El objeto transient de actualizaciones.
     * @return   object    El objeto transient modificado.
     */
    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Obtener información de la última release de GitHub
        $response = $this->get_repository_info();
        if (is_wp_error($response)) {
            return $transient;
        }

        // Comprobar si hay una nueva versión
        $github_version = ltrim($response->tag_name, 'v');
        if (version_compare($github_version, $this->version, '>')) {
            $plugin_data = get_plugin_data($this->plugin_file);
            $plugin_slug = basename($this->plugin_file, '.php');

            $transient->response[$this->plugin_file] = (object) array(
                'slug' => $plugin_slug,
                'plugin' => $this->plugin_file,
                'new_version' => $github_version,
                'url' => $plugin_data['PluginURI'],
                'package' => $response->zipball_url,
            );
        }

        return $transient;
    }

    /**
     * Obtiene información del plugin para la pantalla de detalles.
     *
     * @since    1.0.0
     * @param    false|object|array    $result    El resultado, por defecto false.
     * @param    string                $action    El tipo de información solicitada.
     * @param    object                $args      Argumentos adicionales.
     * @return   false|object|array    La información del plugin.
     */
    public function plugin_info($result, $action, $args) {
        // Comprobar si es nuestro plugin
        if ('plugin_information' !== $action || $args->slug !== $this->plugin_slug) {
            return $result;
        }

        // Obtener información de la última release de GitHub
        $response = $this->get_repository_info();
        if (is_wp_error($response)) {
            return $result;
        }

        $plugin_data = get_plugin_data($this->plugin_file);
        $github_version = ltrim($response->tag_name, 'v');

        $result = (object) array(
            'name' => $plugin_data['Name'],
            'slug' => $this->plugin_slug,
            'version' => $github_version,
            'author' => $plugin_data['Author'],
            'author_profile' => $plugin_data['AuthorURI'],
            'last_updated' => $response->published_at,
            'homepage' => $plugin_data['PluginURI'],
            'short_description' => $plugin_data['Description'],
            'sections' => array(
                'description' => $plugin_data['Description'],
                'changelog' => $response->body,
            ),
            'download_link' => $response->zipball_url,
        );

        return $result;
    }

    /**
     * Acciones después de la instalación.
     *
     * @since    1.0.0
     * @param    bool      $response    Resultado de la instalación.
     * @param    array     $hook_extra  Información adicional.
     * @param    array     $result      Información de la instalación.
     * @return   array     Información de la instalación modificada.
     */
    public function post_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        // Comprobar si es nuestro plugin
        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_file) {
            return $result;
        }

        // Asegurarse de que el sistema de archivos de WordPress está inicializado
        if (!$wp_filesystem) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }
        
        // Obtener el directorio base del plugin
        $plugin_folder = WP_PLUGIN_DIR . '/' . dirname(plugin_basename($this->plugin_file));
        $plugin_folder = trailingslashit($plugin_folder);
        
        // Buscar el directorio del plugin dentro del ZIP extraído
        $source_path = $result['destination'];
        $source_dirs = glob(trailingslashit($source_path) . '*', GLOB_ONLYDIR);
        
        if (count($source_dirs) === 1) {
            // Si hay un solo directorio en la raíz, ese es probablemente el plugin
            $source_path = trailingslashit($source_dirs[0]);
        }
        
        // Mover archivos a la ubicación correcta
        if ($wp_filesystem->is_dir($source_path)) {
            // Limpiar directorio de destino
            $wp_filesystem->delete($plugin_folder, true);
            
            // Copiar archivos
            copy_dir($source_path, $plugin_folder);
            
            // Limpiar directorio temporal
            $wp_filesystem->delete($result['destination'], true);
            
            // Actualizar la ruta de destino
            $result['destination'] = $plugin_folder;
            $result['destination_name'] = dirname(plugin_basename($this->plugin_file));
        }

        return $result;
    }

    /**
     * Obtiene información del repositorio de GitHub.
     *
     * @since    1.0.0
     * @return   object|WP_Error    Información del repositorio o error.
     */
    private function get_repository_info() {
        // Comprobar si tenemos la información en caché
        $cache_key = 'niche_creator_plus_github_api_' . md5($this->github_username . '/' . $this->github_repo);
        $cache = get_transient($cache_key);

        if (false !== $cache) {
            return $cache;
        }

        // Obtener información de la última release de GitHub
        $url = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $this->github_username, $this->github_repo);
        
        // Intentar usar un token de GitHub si está definido
        $headers = array(
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url'),
        );
        
        // Si existe un token de GitHub definido, usarlo para aumentar el límite de tasa
        $github_token = defined('GITHUB_ACCESS_TOKEN') ? GITHUB_ACCESS_TOKEN : false;
        if ($github_token) {
            $headers['Authorization'] = 'token ' . $github_token;
        }
        
        $response = wp_remote_get($url, array(
            'headers' => $headers,
            'timeout' => 10,
        ));

        if (is_wp_error($response)) {
            // Guardar el error en caché durante 1 hora para evitar demasiadas solicitudes fallidas
            set_transient($cache_key, $response, 1 * HOUR_IN_SECONDS);
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            $error = new WP_Error(
                'github_api_error', 
                sprintf('Error al obtener información de GitHub. Código: %s', $code),
                array('response' => $response)
            );
            
            // Guardar el error en caché durante 1 hora
            set_transient($cache_key, $error, 1 * HOUR_IN_SECONDS);
            return $error;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        
        if (empty($data) || !isset($data->tag_name)) {
            $error = new WP_Error(
                'github_api_invalid_response', 
                'Respuesta inválida de GitHub API',
                array('body' => $body)
            );
            
            // Guardar el error en caché durante 1 hora
            set_transient($cache_key, $error, 1 * HOUR_IN_SECONDS);
            return $error;
        }

        // Guardar en caché durante 6 horas
        set_transient($cache_key, $data, 6 * HOUR_IN_SECONDS);

        return $data;
    }
}
