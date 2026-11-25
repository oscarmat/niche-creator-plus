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
     * Ruta del plugin relativa a wp-content/plugins (clave que usa WordPress).
     *
     * @since    1.0.17
     * @access   private
     * @var      string    $plugin_basename
     */
    private $plugin_basename;

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
        $this->plugin_basename = plugin_basename($plugin_file);
        $this->github_username = $github_username;
        $this->github_repo = $github_repo;

        // NO registrar hooks en el constructor
        // Los hooks deben registrarse explícitamente llamando a init()
    }

    /**
     * Registra los hooks necesarios para el sistema de actualización.
     *
     * @since    1.0.13
     */
    public function init() {
        // Añadir filtros para el proceso de actualización
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);
        add_filter('upgrader_pre_download', array($this, 'pre_download'), 10, 3);
        add_filter('upgrader_source_selection', array($this, 'source_selection'), 10, 4);
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

            $transient->response[$this->plugin_basename] = (object) array(
                'slug' => $plugin_slug,
                'plugin' => $this->plugin_basename,
                'new_version' => $github_version,
                'url' => $plugin_data['PluginURI'],
                'package' => $this->get_package_url($response),
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
            'download_link' => $this->get_package_url($response),
        );

        return $result;
    }

    /**
     * Token de GitHub (constante GITHUB_ACCESS_TOKEN en wp-config.php), necesario con repos privados.
     *
     * @since    1.0.17
     * @return   string
     */
    private function get_token() {
        return defined('GITHUB_ACCESS_TOKEN') ? (string) GITHUB_ACCESS_TOKEN : '';
    }

    /**
     * URL del paquete a instalar: el ZIP adjunto a la release (carpeta raíz correcta) o, si no hay, el zipball.
     * Con token se usa la URL de la API del asset, que es la única que sirve en repos privados.
     *
     * @since    1.0.17
     * @param    object    $release    Release de la API de GitHub.
     * @return   string
     */
    private function get_package_url($release) {
        if (!empty($release->assets) && is_array($release->assets)) {
            foreach ($release->assets as $asset) {
                if (isset($asset->name) && preg_match('/^niche-creator-plus.*\.zip$/', $asset->name)) {
                    return $this->get_token() ? $asset->url : $asset->browser_download_url;
                }
            }
        }
        return $release->zipball_url;
    }

    /**
     * Descarga autenticada del paquete cuando es de nuestro repo y hay token.
     *
     * GitHub responde con una redirección a una URL firmada que no admite la cabecera Authorization,
     * así que se pide con redirection=0 y se sigue la redirección sin credenciales.
     *
     * @since    1.0.17
     * @param    bool|string|WP_Error    $reply      Resultado previo (false = descargar WordPress).
     * @param    string                  $package    URL del paquete.
     * @return   bool|string|WP_Error
     */
    public function pre_download($reply, $package) {
        $prefix = sprintf('https://api.github.com/repos/%s/%s/', $this->github_username, $this->github_repo);
        $token = $this->get_token();
        if (!$token || !is_string($package) || strpos($package, $prefix) !== 0) {
            return $reply;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';

        $response = wp_remote_get($package, array(
            'headers' => array(
                'Accept' => 'application/octet-stream',
                'Authorization' => 'token ' . $token,
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url'),
            ),
            'redirection' => 0,
            'timeout' => 60,
        ));
        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        if (in_array($code, array(301, 302, 303, 307, 308), true)) {
            $location = wp_remote_retrieve_header($response, 'location');
            if (!is_string($location) || strpos($location, 'https://') !== 0) {
                return new WP_Error('github_download_redirect', 'Redirección de descarga no válida.');
            }
            return download_url($location, 300);
        }
        if (200 === $code) {
            $tmp = wp_tempnam($package);
            if (!$tmp || false === file_put_contents($tmp, wp_remote_retrieve_body($response))) {
                return new WP_Error('github_download_write', 'No se pudo guardar el paquete descargado.');
            }
            return $tmp;
        }
        return new WP_Error('github_download_error', sprintf('Error al descargar el paquete de GitHub. Código: %s', $code));
    }

    /**
     * Garantiza que la carpeta extraída se llame como el plugin (un zipball trae owner-repo-sha).
     *
     * @since    1.0.17
     * @param    string        $source           Carpeta extraída.
     * @param    string        $remote_source    Carpeta temporal que la contiene.
     * @param    WP_Upgrader   $upgrader         Instancia del actualizador.
     * @param    array         $hook_extra       Datos de la actualización.
     * @return   string|WP_Error
     */
    public function source_selection($source, $remote_source, $upgrader = null, $hook_extra = array()) {
        global $wp_filesystem;

        if (empty($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_basename) {
            return $source;
        }

        $deseada = trailingslashit($remote_source) . $this->plugin_slug . '/';
        if (trailingslashit($source) === $deseada) {
            return $source;
        }
        if (!$wp_filesystem->exists(trailingslashit($source) . basename($this->plugin_file))) {
            return new WP_Error('ncp_paquete_invalido', 'El paquete no contiene ' . basename($this->plugin_file) . '.');
        }
        if (!$wp_filesystem->move($source, $deseada, true)) {
            return new WP_Error('ncp_renombrar', 'No se pudo renombrar la carpeta del plugin.');
        }
        return $deseada;
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
