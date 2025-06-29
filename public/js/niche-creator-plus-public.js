/**
 * Todo el JavaScript para el área pública.
 *
 * @package    Niche_Creator_Plus
 * @subpackage Niche_Creator_Plus/public/js
 */

(function($) {
    'use strict';

    /**
     * Todo el código JavaScript para el área pública debe
     * estar contenido dentro de esta función.
     */
    $(document).ready(function() {

        // Ejemplo de funcionalidad para el frontend
        $('.niche-creator-button').on('click', function(e) {
            // Si el botón tiene un atributo data-action, podemos usarlo para determinar qué hacer
            const action = $(this).data('action');
            if (action) {
                switch(action) {
                    case 'toggle-content':
                        $(this).prev('.niche-creator-content').slideToggle();
                        break;
                    case 'load-more':
                        // Aquí podríamos implementar una carga AJAX de más contenido
                        $(this).text('Cargando...');
                        setTimeout(function() {
                            // Simulación de carga
                            $('.niche-creator-container').append('<p>Contenido adicional cargado dinámicamente.</p>');
                            $(this).text('Cargar más');
                        }.bind(this), 1000);
                        break;
                }
            }
        });

        // Función para resaltar palabras clave en el contenido
        function highlightKeywords(container, keywords) {
            if (!keywords || !keywords.length) return;
            
            const content = $(container).html();
            let highlightedContent = content;
            
            keywords.forEach(function(keyword) {
                const regex = new RegExp('\\b(' + keyword + ')\\b', 'gi');
                highlightedContent = highlightedContent.replace(regex, '<span class="niche-creator-keyword-highlight">$1</span>');
            });
            
            $(container).html(highlightedContent);
        }

        // Ejemplo de uso de la función de resaltado
        // highlightKeywords('.niche-creator-content', ['palabra1', 'palabra2']);

    });

})(jQuery);
