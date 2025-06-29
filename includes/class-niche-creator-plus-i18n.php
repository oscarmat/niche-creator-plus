<?php
/**
 * Define la funcionalidad de internacionalización
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Niche_Creator_Plus
 */

/**
 * Define la funcionalidad de internacionalización.
 *
 * Carga y define los archivos de internacionalización para este plugin
 * para que esté listo para la traducción.
 *
 * @since      1.0.0
 * @package    Niche_Creator_Plus
 * @author     Niche Creator Team
 */
class Niche_Creator_Plus_i18n {

    /**
     * Carga el dominio de texto del plugin para la traducción.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
            'niche-creator-plus',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );

    }

}
