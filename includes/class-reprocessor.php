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
     * Hooks cuyas acciones llevan el escrapeo en los args: hook => posición del argumento.
     *
     * @since    1.0.18
     * @access   private
     * @var      array
     */
    private $hooks_con_escrapeo = [ 'cargar_categorias' => 3 ];

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
            'cargar_categorias',
        ];
    }

    /**
     * Segundos entre acciones reprogramadas de un hook. Las tareas ligeras (categorías) van más juntas.
     * Filtrable con `niche_creator_plus_intervalo`.
     *
     * @since    1.0.17
     * @param    string    $hook    Hook a reprogramar
     * @return   int
     */
    public function get_intervalo( $hook ) {
        $intervalos = [ 'cargar_categorias' => 10 ];
        $intervalo = isset( $intervalos[ $hook ] ) ? $intervalos[ $hook ] : 120;
        return max( 1, (int) apply_filters( 'niche_creator_plus_intervalo', $intervalo, $hook ) );
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

            // Validar que el hook esté en la lista permitida
            $allowed_hooks = $this->get_hooks();
            if (!in_array($hook, $allowed_hooks)) {
                wp_die(esc_html__('Hook no válido', 'niche-creator-plus'));
            }

            // Si el grupo es __NULL__, lo convertimos a null para la consulta SQL
            if ($group === '__NULL__') {
                $group = null;
            }

            // Reprocesar acciones con el hook y grupo específicos
            $reprogramadas = $this->reprocesar_acciones_fallidas_por_hook_y_grupo( $hook, $group );

            $metadata = $reprogramadas['_metadata'] ?? [];
            $total_reprogramadas = $metadata['total_reprogramadas'] ?? 0;
            $total_encontradas = $metadata['total_encontradas'] ?? 0;
            $debug_info = $metadata['debug_info'] ?? '';
            $errores = $metadata['errores'] ?? [];
            
            // Determinar tipo de notificación según el resultado
            $notice_class = $total_reprogramadas > 0 ? 'notice-success' : 'notice-warning';
            
            echo '<div class="notice ' . $notice_class . '"><p>';
            echo '<strong>Reprogramación completada para:</strong> ' . esc_html( $hook ) . ' (Grupo: ' . ($group === null ? '<em>Sin grupo</em>' : esc_html( $group )) . ')<br>';
            
            echo 'Total reprogramadas: <strong>' . intval($total_reprogramadas) . '</strong> de ' . intval($total_encontradas) . ' encontradas<br>';

            $total_omitidas = $metadata['total_omitidas'] ?? 0;
            if ( $total_omitidas > 0 ) {
                echo 'Omitidas (escrapeo sin filas en la tabla, siguen como fallidas): <strong>' . intval( $total_omitidas ) . '</strong><br>';
            }

            // Mostrar información de depuración si está disponible
            if (!empty($debug_info)) {
                echo '<small>Depuración: ' . esc_html($debug_info) . '</small>';
            }

            echo '</p></div>';

            // Mostrar detalles solo si hay acciones reprogramadas
            if ($total_reprogramadas > 0 && isset($reprogramadas['acciones']) && count($reprogramadas['acciones']) > 0) {
                echo '<div class="card"><h3>Detalles de acciones reprogramadas (' . intval($total_reprogramadas) . ')</h3>';
                echo '<pre>' . esc_html( var_export( $reprogramadas['acciones'], true ) ) . '</pre></div>';
            }
            
            // Mostrar errores si existen
            if (!empty($errores)) {
                echo '<div class="notice notice-error"><p>';
                echo '<strong>⚠️ Se encontraron ' . count($errores) . ' errores al reprogramar:</strong></p>';
                echo '<ul style="margin-left: 20px;">';
                foreach ($errores as $error) {
                    echo '<li>';
                    echo 'Acción #' . intval($error['action_id']) . ': ' . esc_html($error['motivo']);
                    echo '</li>';
                }
                echo '</ul>';
                echo '<details><summary>Ver detalles técnicos</summary>';
                echo '<pre style="background: #f5f5f5; padding: 10px; overflow: auto;">' . esc_html( var_export( $errores, true ) ) . '</pre>';
                echo '</details>';
                echo '</div>';
            }
        }

        // Resumen de errores por hook
        echo '<h2>Resumen de eventos fallidos</h2>';
        echo '<table class="widefat"><thead><tr><th>Hook</th><th>Grupo</th><th>Acciones fallidas</th><th>Acción</th></tr></thead><tbody>';
        
        $resumen = $this->get_resumen_fallidas();
        if (empty($resumen)) {
            echo '<tr><td colspan="4">No hay eventos fallidos para reprocesar</td></tr>';
        } else {
            foreach ( $resumen as $fila ) {
                echo '<tr>';
                echo '<td>' . esc_html( $fila['hook'] ) . '</td>';
                echo '<td>' . (empty($fila['group']) ? '<em>Sin grupo</em>' : esc_html( $fila['group'] )) . '</td>';
                $por_escrapeo = $this->contar_fallidas_por_escrapeo( $fila['hook'], empty( $fila['group'] ) ? null : $fila['group'] );
                echo '<td>' . intval( $fila['count'] );
                if ( $por_escrapeo && $por_escrapeo['inactivas'] > 0 ) {
                    echo ' <small>(' . intval( $por_escrapeo['activas'] ) . ' reprogramables; '
                        . intval( $por_escrapeo['inactivas'] ) . ' de un escrapeo sin filas)</small>';
                }
                echo '</td>';
                echo '<td>';
                // Un formulario por fila: así el grupo enviado es siempre el de la fila pulsada
                echo '<form method="post">';
                echo '<input type="hidden" name="nichecreator_plus_nonce" value="' . esc_attr( wp_create_nonce( 'nichecreator_plus_accion' ) ) . '">';
                $deshabilitar = ( $por_escrapeo && $por_escrapeo['activas'] === 0 ) ? ' disabled' : '';
                echo '<button type="submit" name="nichecreator_plus_hook" value="' . esc_attr( $fila['hook'] ) . '" class="button button-primary"' . $deshabilitar . '>';
                echo 'Reprocesar';
                echo '</button>';
                // Usamos un valor especial para indicar NULL cuando el grupo está vacío
                $group_value = empty($fila['group']) ? '__NULL__' : $fila['group'];
                echo '<input type="hidden" name="nichecreator_plus_group" value="' . esc_attr( $group_value ) . '">';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
        }
        echo '</tbody></table>';
        
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
        $intervalo = $this->get_intervalo( $hook );
        $debug_info = '';

        $debug_info = $group
            ? 'Ejecutando búsqueda con grupo específico: ' . $group
            : 'Ejecutando búsqueda sin grupo específico (grupo NULL)';
        $acciones = $this->get_acciones_fallidas( $hook, $group );
        $total_acciones = count($acciones);

        // Inicializar estructura de resultado con metadata
        $resultado = [
            '_metadata' => [
                'debug_info' => $debug_info,
                'total_encontradas' => $total_acciones,
                'total_reprogramadas' => 0
            ],
            'acciones' => []
        ];

        $errores = [];
        $omitidas = [];
        $activos = $this->get_escrapeos_activos();
        $n = 0; // posición entre las realmente reprogramadas (para espaciarlas)
        
        foreach ( $acciones as $accion ) {
            $args = $this->decodificar_args( $accion->args );

            if ( is_array( $args ) && ! $this->esta_activa( $hook, $args, $activos ) ) {
                // Escrapeo sin filas en niche_tabla: no generaría nada; se deja como fallida
                $omitidas[] = [ 'action_id' => $accion->action_id, 'args' => $args ];
                continue;
            }

            if ( is_array( $args ) ) {
                $timestamp = $inicio + ( $n * $intervalo );
                $n++;

                $nuevo_id = as_schedule_single_action(
                    $timestamp,
                    $hook,
                    $args,
                    'niche_creator_plus'
                );

                if ( $nuevo_id ) {
                    $resultado['acciones'][] = $args;
                    $resultado['_metadata']['total_reprogramadas']++;

                    // Cancelar acción original (API de Action Scheduler; UPDATE directo solo como último recurso)
                    $cancelada = false;
                    if ( class_exists( 'ActionScheduler' ) && ActionScheduler::is_initialized() ) {
                        try {
                            ActionScheduler::store()->cancel_action( $accion->action_id );
                            $cancelada = true;
                        } catch ( Exception $e ) {
                            error_log( "Niche Creator Plus: no se pudo cancelar {$accion->action_id} con la API: " . $e->getMessage() );
                        }
                    }
                    if ( ! $cancelada ) {
                        $wpdb->update(
                            "{$wpdb->prefix}actionscheduler_actions",
                            [ 'status' => 'canceled' ],
                            [ 'action_id' => $accion->action_id ],
                            [ '%s' ],
                            [ '%d' ]
                        );
                    }

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
                } else {
                    // Registrar error si no se pudo reprogramar
                    $errores[] = [
                        'action_id' => $accion->action_id,
                        'motivo' => 'as_schedule_single_action devolvió false/0',
                        'args' => $args,
                        'timestamp' => $timestamp
                    ];
                    error_log("Niche Creator Plus: Error al reprogramar acción {$accion->action_id} para hook {$hook}");
                }
            } else {
                // Determinar el motivo específico del error
                $motivo = 'Args no es un array';
                if ( is_string( $accion->args ) ) {
                    $json_error = json_last_error();
                    if ( $json_error !== JSON_ERROR_NONE ) {
                        $motivo = 'Formato inválido (no es PHP serializado ni JSON válido)';
                    }
                }
                
                $errores[] = [
                    'action_id' => $accion->action_id,
                    'motivo' => $motivo,
                    'args_raw' => $accion->args,
                    'tipo_recibido' => gettype($args)
                ];
            }
        }
        
        $resultado['_metadata']['total_omitidas'] = count( $omitidas );
        if ( ! empty( $omitidas ) ) {
            $resultado['_metadata']['omitidas'] = $omitidas;
        }

        // Agregar errores a la metadata si existen
        if (!empty($errores)) {
            $resultado['_metadata']['errores'] = $errores;
        }

        return $resultado;
    }

    /**
     * Acciones fallidas de un hook y grupo (grupo null = sin grupo).
     *
     * @since    1.0.18
     * @param    string         $hook     Hook
     * @param    string|null    $group    Slug del grupo o null
     * @return   array          Filas con action_id y args
     */
    private function get_acciones_fallidas( $hook, $group = null ) {
        global $wpdb;

        if ( $group ) {
            return $wpdb->get_results( $wpdb->prepare(
                "SELECT a.action_id, a.args
                 FROM {$wpdb->prefix}actionscheduler_actions a
                 INNER JOIN {$wpdb->prefix}actionscheduler_groups g ON a.group_id = g.group_id
                 WHERE a.hook = %s AND a.status = 'failed' AND g.slug = %s
                 ORDER BY a.action_id ASC",
                $hook, $group
            ) );
        }
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT a.action_id, a.args
             FROM {$wpdb->prefix}actionscheduler_actions a
             LEFT JOIN {$wpdb->prefix}actionscheduler_groups g ON a.group_id = g.group_id
             WHERE a.hook = %s AND a.status = 'failed' AND (g.slug IS NULL OR g.slug = '')
             ORDER BY a.action_id ASC",
            $hook
        ) );
    }

    /**
     * Args de una acción de Action Scheduler (PHP serializado o JSON) o null si no son un array.
     *
     * @since    1.0.18
     * @param    string    $raw    Columna args
     * @return   array|null
     */
    private function decodificar_args( $raw ) {
        $args = maybe_unserialize( $raw );
        if ( is_array( $args ) ) {
            return $args;
        }
        $json = json_decode( $raw, true );
        return ( json_last_error() === JSON_ERROR_NONE && is_array( $json ) ) ? $json : null;
    }

    /**
     * Escrapeos con filas en niche_tabla (los que pueden generar algo ahora), o null si no se puede saber
     * (sin tabla de Niche Creator o error de consulta): entonces no se filtra.
     *
     * @since    1.0.18
     * @return   array|null
     */
    public function get_escrapeos_activos() {
        global $wpdb;

        $tabla = $wpdb->prefix . 'niche_tabla';
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $tabla ) ) ) !== $tabla ) {
            return null;
        }
        $wpdb->last_error = '';
        $escrapeos = $wpdb->get_col( "SELECT DISTINCT escrapeo FROM `{$tabla}`" );
        if ( $wpdb->last_error !== '' || ! is_array( $escrapeos ) ) {
            return null;
        }
        return array_map( 'strval', $escrapeos );
    }

    /**
     * Indica si una acción fallida debe reprogramarse: solo las de hooks con escrapeo en los args
     * (hoy cargar_categorias, 4.º arg) se limitan a escrapeos con filas; el resto siempre.
     *
     * @since    1.0.18
     * @param    string        $hook      Hook
     * @param    array         $args      Args de la acción
     * @param    array|null    $activos   Resultado de get_escrapeos_activos()
     * @return   bool
     */
    private function esta_activa( $hook, $args, $activos ) {
        if ( $activos === null || ! isset( $this->hooks_con_escrapeo[ $hook ] ) ) {
            return true;
        }
        $indice = $this->hooks_con_escrapeo[ $hook ];
        if ( ! isset( $args[ $indice ] ) ) {
            return true;
        }
        return in_array( (string) $args[ $indice ], $activos, true );
    }

    /**
     * Cuántas fallidas de un hook y grupo son reprogramables ahora y cuántas son de escrapeos sin filas.
     *
     * @since    1.0.18
     * @param    string         $hook    Hook
     * @param    string|null    $group   Slug del grupo o null
     * @return   array|null     [ 'activas' => n, 'inactivas' => n ], o null si el hook no se filtra
     */
    private function contar_fallidas_por_escrapeo( $hook, $group ) {
        $activos = $this->get_escrapeos_activos();
        if ( $activos === null || ! isset( $this->hooks_con_escrapeo[ $hook ] ) ) {
            return null;
        }
        $cuenta = [ 'activas' => 0, 'inactivas' => 0 ];
        foreach ( $this->get_acciones_fallidas( $hook, $group ) as $accion ) {
            $args = $this->decodificar_args( $accion->args );
            $activa = ! is_array( $args ) || $this->esta_activa( $hook, $args, $activos );
            $cuenta[ $activa ? 'activas' : 'inactivas' ]++;
        }
        return $cuenta;
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