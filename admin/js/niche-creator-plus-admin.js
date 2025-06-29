/**
 * Todo el JavaScript para el área de administración.
 *
 * @package    Niche_Creator_Plus
 * @subpackage Niche_Creator_Plus/admin/js
 */

(function($) {
    'use strict';

    /**
     * Todo el código JavaScript para el área de administración debe
     * estar contenido dentro de esta función.
     */
    $(document).ready(function() {

        // Manejar el rango de densidad de palabras clave
        $('#niche_creator_plus_keyword_density').on('input', function() {
            const value = $(this).val();
            $(this).next('.description').text(`${value} (${value === '1' ? 'Baja' : value === '5' ? 'Alta' : 'Media'})`);
        });

        // Manejar los botones de acción rápida
        $('.niche-creator-actions .button').on('click', function(e) {
            e.preventDefault();
            const action = $(this).text().trim();
            
            // Aquí se puede implementar la lógica para cada acción
            switch(action) {
                case 'Crear nuevo sitio':
                    alert('Funcionalidad de creación de sitio en desarrollo');
                    break;
                case 'Generar contenido':
                    alert('Funcionalidad de generación de contenido en desarrollo');
                    break;
                case 'Investigar palabras clave':
                    alert('Funcionalidad de investigación de palabras clave en desarrollo');
                    break;
                default:
                    console.log('Acción no reconocida');
            }
        });

    });

})(jQuery);
