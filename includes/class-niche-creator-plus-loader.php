<?php
/**
 * Registra todas las acciones y filtros para el plugin
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Niche_Creator_Plus
 */

/**
 * Registra todas las acciones y filtros para el plugin.
 *
 * Mantiene una lista de todos los hooks que están registrados en todo
 * el plugin, y los registra con el WordPress API. Llama a la
 * función run para ejecutar la lista de acciones y filtros.
 *
 * @package    Niche_Creator_Plus
 * @author     Niche Creator Team
 */
class Niche_Creator_Plus_Loader {

    /**
     * El array de acciones registradas con WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $actions    Las acciones registradas con WordPress para ejecutar cuando se carga el plugin.
     */
    protected $actions;

    /**
     * El array de filtros registrados con WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $filters    Los filtros registrados con WordPress para ejecutar cuando se carga el plugin.
     */
    protected $filters;

    /**
     * Inicializa las colecciones utilizadas para mantener las acciones y los filtros.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->actions = array();
        $this->filters = array();

    }

    /**
     * Añade una nueva acción al array de acciones que se registrarán con WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             El nombre de la acción de WordPress que se está registrando.
     * @param    object               $component        Una referencia al objeto de instancia.
     * @param    string               $callback         El nombre de la función definida en el componente.
     * @param    int                  $priority         Opcional. La prioridad en la que se debe ejecutar la función. Por defecto es 10.
     * @param    int                  $accepted_args    Opcional. El número de argumentos que debería aceptar la función. Por defecto es 1.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Añade un nuevo filtro al array de filtros que se registrarán con WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             El nombre del filtro de WordPress que se está registrando.
     * @param    object               $component        Una referencia al objeto de instancia.
     * @param    string               $callback         El nombre de la función definida en el componente.
     * @param    int                  $priority         Opcional. La prioridad en la que se debe ejecutar la función. Por defecto es 10.
     * @param    int                  $accepted_args    Opcional. El número de argumentos que debería aceptar la función. Por defecto es 1.
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Una utilidad para registrar los hooks en las colecciones.
     *
     * @since    1.0.0
     * @access   private
     * @param    array                $hooks            La colección de hooks que se está registrando (es decir, acciones o filtros).
     * @param    string               $hook             El nombre del filtro de WordPress que se está registrando.
     * @param    object               $component        Una referencia al objeto de instancia.
     * @param    string               $callback         El nombre de la función definida en el componente.
     * @param    int                  $priority         La prioridad en la que se debe ejecutar la función.
     * @param    int                  $accepted_args    El número de argumentos que debería aceptar la función.
     * @return   array                                  La colección de acciones y filtros registrados con WordPress.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {

        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;

    }

    /**
     * Registra los filtros y acciones con WordPress.
     *
     * @since    1.0.0
     */
    public function run() {

        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

    }

}
