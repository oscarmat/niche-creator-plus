<?php
/**
 * Proporciona una vista de área de administración para el plugin
 *
 * Este archivo se utiliza para marcar los aspectos del plugin que se muestran en el área de administración.
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
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <div class="niche-creator-dashboard">
        <div class="niche-creator-card">
            <h2>Bienvenido a Niche Creator Plus</h2>
            <p>Esta es la herramienta definitiva para crear y gestionar sitios web de nicho. Utiliza las opciones del menú lateral para acceder a las diferentes funcionalidades.</p>
        </div>
        
        <div class="niche-creator-stats">
            <h3>Estadísticas</h3>
            <div class="stat-container">
                <div class="stat-box">
                    <span class="stat-number">0</span>
                    <span class="stat-label">Sitios creados</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number">0</span>
                    <span class="stat-label">Artículos generados</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number">0</span>
                    <span class="stat-label">Palabras clave investigadas</span>
                </div>
            </div>
        </div>
        
        <div class="niche-creator-actions">
            <h3>Acciones rápidas</h3>
            <a href="#" class="button button-primary">Crear nuevo sitio</a>
            <a href="#" class="button button-secondary">Generar contenido</a>
            <a href="#" class="button button-secondary">Investigar palabras clave</a>
        </div>
    </div>
</div>
