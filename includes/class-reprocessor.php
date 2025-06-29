<?php
/**
 * La clase para reprocesar acciones fallidas de Action Scheduler
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Niche_Creator_Plus
 * @subpackage Niche_Creator_Plus/includes
 */

/**
 * La clase para reprocesar acciones fallidas de Action Scheduler.
 *
 * Esta clase contiene la lógica para reprocesar acciones fallidas
 * relacionadas con Niche Creator.
 *
 * @since      1.0.0
 * @package    Niche_Creator_Plus
 * @subpackage Niche_Creator_Plus/includes
 * @author     Niche Creator Team
 */
class Niche_Creator_Plus_Reprocessor {

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
     * Obtiene la lista de hooks que se gestionan
     *
     * @since    1.0.0
     * @return   array    Lista de hooks gestionados
     */
    public function get_hooks() {
        return [
            'crear_duplicados',
            'cargar_imagenesia',
            'nichecreator_cargageografica',
            'cargar_geograficos',
            'cargar_scrape',
            'cargar_ia',
        ];
    }

    /**
     * Renderiza la página principal del reprocesador
     *
     * @since    1.0.0
     */
    public function render_page() {
        echo '<div class="wrap"><h1>Niche Creator Plus</h1>';

        // Procesar botón si se ha pulsado
        if ( isset( $_POST['nichecreator_plus_hook'] ) && isset( $_POST['nichecreator_plus_group'] ) && check_admin_referer( 'nichecreator_plus_accion', 'nichecreator_plus_nonce' ) ) {
            $hook = sanitize_text_field( $_POST['nichecreator_plus_hook'] );
            $group = sanitize_text_field( $_POST['nichecreator_plus_group'] );
            
            // Si el grupo es __NULL__, lo convertimos a null para la consulta SQL
            if ($group === '__NULL__') {
                $group = null;
            }
            
            // Reprocesar acciones con el hook y grupo específicos
            $reprogramadas = $this->reprocesar_acciones_fallidas_por_hook_y_grupo( $hook, $group );

            echo '<div class="notice notice-success"><p>';
            echo '<strong>Reprogramación completada para:</strong> ' . esc_html( $hook ) . ' (Grupo: ' . esc_html( $group ) . ')<br>';
            echo 'Total reprogramadas: ' . count( $reprogramadas );
            echo '</p></div>';
            
            if ( count( $reprogramadas ) > 0 ) {
                echo '<div class="card"><h3>Detalles de acciones reprogramadas</h3>';
                echo '<pre>' . esc_html( var_export( $reprogramadas, true ) ) . '</pre></div>';
            }
        }

        // Resumen de errores por hook
        echo '<h2>Resumen de eventos fallidos</h2>';
        echo '<form method="post">';
        wp_nonce_field( 'nichecreator_plus_accion', 'nichecreator_plus_nonce' );
        echo '<table class="widefat"><thead><tr><th>Hook</th><th>Grupo</th><th>Acciones fallidas</th><th>Acción</th></tr></thead><tbody>';
        
        $resumen = $this->get_resumen_fallidas();
        if (empty($resumen)) {
            echo '<tr><td colspan="4">No hay eventos fallidos para reprocesar</td></tr>';
        } else {
            foreach ( $resumen as $fila ) {
                echo '<tr>';
                echo '<td>' . esc_html( $fila['hook'] ) . '</td>';
                echo '<td>' . (empty($fila['group']) ? '<em>Sin grupo</em>' : esc_html( $fila['group'] )) . '</td>';
                echo '<td>' . intval( $fila['count'] ) . '</td>';
                echo '<td>';
                echo '<button type="submit" name="nichecreator_plus_hook" value="' . esc_attr( $fila['hook'] ) . '" class="button button-primary">';
                echo 'Reprocesar';
                echo '</button>';
                // Usamos un valor especial para indicar NULL cuando el grupo está vacío
                $group_value = empty($fila['group']) ? '__NULL__' : $fila['group'];
                echo '<input type="hidden" name="nichecreator_plus_group" value="' . esc_attr( $group_value ) . '">';
                echo '</td>';
                echo '</tr>';
            }
        }
        echo '</tbody></table>';
        echo '</form>';
        
        // Ya no necesitamos los botones individuales por hook, ya que ahora tenemos botones en cada fila
        // Los eliminamos para evitar duplicidad de funcionalidad
        echo '</div>';
    }

    /**
     * Reprocesa las acciones fallidas para un hook específico
     *
     * @since    1.0.0
     * @param    string    $hook    El hook a reprocesar
     * @return   array     Resultado de las acciones reprocesadas
     */
    public function reprocesar_acciones_fallidas_por_hook( $hook ) {
        // Llamar al método más específico sin filtrar por grupo
        return $this->reprocesar_acciones_fallidas_por_hook_y_grupo($hook, null);
    }
    
    /**
     * Reprocesa las acciones fallidas para un hook y grupo específicos
     *
     * @since    1.0.7
     * @param    string    $hook     El hook a reprocesar
     * @param    string    $group    El grupo a reprocesar (opcional)
     * @return   array     Resultado de las acciones reprocesadas
     */
    public function reprocesar_acciones_fallidas_por_hook_y_grupo( $hook, $group = null ) {
        // Verificar que Action Scheduler esté disponible
        if (!function_exists('as_schedule_single_action')) {
            return array('error' => 'Action Scheduler no está disponible. Este plugin requiere que Niche Creator esté activado.');
        }
        
        global $wpdb;

        // Obtener timestamp de la última acción pendiente en nuestro grupo
        $ultimo = $wpdb->get_var(
            "SELECT MAX(scheduled_date_gmt)
             FROM {$wpdb->prefix}actionscheduler_actions a
             INNER JOIN {$wpdb->prefix}actionscheduler_groups g ON a.group_id = g.group_id
             WHERE g.slug = 'niche_creator_plus' AND a.status = 'pending'"
        );

        $inicio = $ultimo ? strtotime( $ultimo ) : time();
        $intervalo = 120; // segundos
        $resultado = [];

        // Obtener acciones fallidas
        if ($group) {
            // Si se especifica un grupo, filtrar por hook y grupo
            $acciones = $wpdb->get_results( $wpdb->prepare(
                "SELECT a.action_id, a.args
                 FROM {$wpdb->prefix}actionscheduler_actions a
                 INNER JOIN {$wpdb->prefix}actionscheduler_groups g ON a.group_id = g.group_id
                 WHERE a.hook = %s AND a.status = 'failed' AND g.slug = %s
                 ORDER BY a.action_id ASC",
                $hook, $group
            ) );
        } else {
            // Si no se especifica grupo (grupo es NULL), filtrar por hook y grupo NULL
            $acciones = $wpdb->get_results( $wpdb->prepare(
                "SELECT a.action_id, a.args
                 FROM {$wpdb->prefix}actionscheduler_actions a
                 LEFT JOIN {$wpdb->prefix}actionscheduler_groups g ON a.group_id = g.group_id
                 WHERE a.hook = %s AND a.status = 'failed' AND (g.slug IS NULL OR g.slug = '')
                 ORDER BY a.action_id ASC",
                $hook
            ) );
        }

        foreach ( $acciones as $i => $accion ) {
            $args = maybe_unserialize( $accion->args );
            if ( is_array( $args ) ) {
                $timestamp = $inicio + ( $i * $intervalo );

                $nuevo_id = as_schedule_single_action(
                    $timestamp,
                    $hook,
                    $args,
                    'niche_creator_plus'
                );

                if ( $nuevo_id ) {
                    $resultado[] = $args;

                    // Cancelar acción original
                    $wpdb->update(
                        "{$wpdb->prefix}actionscheduler_actions",
                        [ 'status' => 'canceled' ],
                        [ 'action_id' => $accion->action_id ],
                        [ '%s' ],
                        [ '%d' ]
                    );

                    // Añadir log
                    $wpdb->insert(
                        "{$wpdb->prefix}actionscheduler_logs",
                        [
                            'action_id' => $accion->action_id,
                            'message'   => 'Reprogramada desde Niche Creator Plus',
                            'log_date_gmt' => current_time( 'mysql', true ),
                            'log_date_local' => current_time( 'mysql' ),
                        ],
                        [ '%d', '%s', '%s', '%s' ]
                    );
                }
            }
        }

        return $resultado;
    }

    /**
     * Obtiene un resumen agrupado de acciones fallidas por hook y grupo
     *
     * @since    1.0.0
     * @return   array    Resumen de acciones fallidas
     */
    public function get_resumen_fallidas() {
        global $wpdb;

        $hooks = $this->get_hooks();
        $placeholder = implode( ',', array_fill( 0, count( $hooks ), '%s' ) );

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT a.hook, g.slug AS 'group', COUNT(*) as count
             FROM {$wpdb->prefix}actionscheduler_actions a
             LEFT JOIN {$wpdb->prefix}actionscheduler_groups g ON a.group_id = g.group_id
             WHERE a.status = 'failed' AND a.hook IN ($placeholder)
             GROUP BY a.hook, g.slug
             ORDER BY a.hook ASC",
            $hooks
        ), ARRAY_A );
    }
}

// === Hooks que quieres gestionar ===
function nichecreator_plus_get_hooks() {
    $reprocessor = new Niche_Creator_Plus_Reprocessor('nichecreator_plus', '1.0.0');
    return $reprocessor->get_hooks();
}

// === Página principal del menú ===
// Nota: La función nichecreator_plus_render_page se ha movido a class-admin-menu.php para evitar duplicación

// === Función reutilizable para reprocesar fallidas ===
function reprocesar_acciones_fallidas_por_hook( $hook ) {
    $reprocessor = new Niche_Creator_Plus_Reprocessor('nichecreator_plus', '1.0.0');
    return $reprocessor->reprocesar_acciones_fallidas_por_hook( $hook );
}

// === Obtener resumen agrupado por hook y grupo ===
function nichecreator_plus_get_resumen_fallidas() {
    $reprocessor = new Niche_Creator_Plus_Reprocessor('nichecreator_plus', '1.0.0');
    return $reprocessor->get_resumen_fallidas();
}