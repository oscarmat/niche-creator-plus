<?php
/**
 * Proporciona una vista de configuración de administración para el plugin
 *
 * Este archivo se utiliza para marcar los aspectos de configuración del plugin que se muestran en el área de administración.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Niche_Creator_Plus
 * @subpackage Niche_Creator_Plus/admin/partials
 */
?>

<!-- Este archivo debe consistir principalmente en HTML con un poco de PHP. -->
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?> - Configuración</h1>
    
    <form method="post" action="options.php">
        <?php
        // Este formulario de configuración utilizará el grupo de opciones 'niche_creator_plus_options'
        settings_fields('niche_creator_plus_options');
        do_settings_sections('niche_creator_plus_settings');
        submit_button('Guardar configuración');
        ?>
        
        <h2>Configuración general</h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">Modo de operación</th>
                <td>
                    <select name="niche_creator_plus_mode" id="niche_creator_plus_mode">
                        <option value="auto">Automático</option>
                        <option value="manual">Manual</option>
                        <option value="semi">Semi-automático</option>
                    </select>
                    <p class="description">Selecciona cómo quieres que funcione el plugin.</p>
                </td>
            </tr>
            <tr>
                <th scope="row">API Key</th>
                <td>
                    <input type="text" name="niche_creator_plus_api_key" id="niche_creator_plus_api_key" class="regular-text" placeholder="Introduce tu API key">
                    <p class="description">API key para servicios externos (si es necesario).</p>
                </td>
            </tr>
        </table>
        
        <h2>Configuración de contenido</h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">Longitud de artículos</th>
                <td>
                    <select name="niche_creator_plus_article_length" id="niche_creator_plus_article_length">
                        <option value="short">Corto (500-800 palabras)</option>
                        <option value="medium" selected>Medio (800-1500 palabras)</option>
                        <option value="long">Largo (1500-2500 palabras)</option>
                        <option value="very_long">Muy largo (2500+ palabras)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">Estilo de escritura</th>
                <td>
                    <select name="niche_creator_plus_writing_style" id="niche_creator_plus_writing_style">
                        <option value="informative">Informativo</option>
                        <option value="conversational">Conversacional</option>
                        <option value="professional">Profesional</option>
                        <option value="casual">Casual</option>
                    </select>
                </td>
            </tr>
        </table>
        
        <h2>Configuración SEO</h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">Densidad de palabras clave</th>
                <td>
                    <input type="range" name="niche_creator_plus_keyword_density" id="niche_creator_plus_keyword_density" min="1" max="5" value="3">
                    <span class="description">1 (Baja) - 5 (Alta)</span>
                </td>
            </tr>
            <tr>
                <th scope="row">Generar meta descripciones</th>
                <td>
                    <input type="checkbox" name="niche_creator_plus_generate_meta" id="niche_creator_plus_generate_meta" value="1" checked>
                    <label for="niche_creator_plus_generate_meta">Generar automáticamente meta descripciones optimizadas</label>
                </td>
            </tr>
        </table>
    </form>
</div>
