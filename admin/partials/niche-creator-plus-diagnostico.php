<?php
/**
 * Página de diagnóstico para el plugin
 *
 * @link       https://example.com
 * @since      1.0.2
 *
 * @package    Niche_Creator_Plus
 * @subpackage Niche_Creator_Plus/admin/partials
 */

// Si se accede directamente, abortar
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="card">
        <h2>Información del sistema</h2>
        <p>Versión de PHP: <?php echo phpversion(); ?></p>
        <p>Versión de WordPress: <?php echo get_bloginfo('version'); ?></p>
        <p>Versión del plugin: <?php echo NICHE_CREATOR_PLUS_VERSION; ?></p>
    </div>
    
    <div class="card">
        <h2>Verificación de dependencias</h2>
        <?php if (function_exists('as_schedule_single_action')): ?>
            <p>✅ Action Scheduler está disponible</p>
        <?php else: ?>
            <p>❌ <strong>ERROR:</strong> Action Scheduler no está disponible. Este plugin requiere que el plugin principal Niche Creator esté activo.</p>
        <?php endif; ?>
        
        <?php 
        if (function_exists('is_plugin_active')) {
            if (is_plugin_active('niche-creator/niche-creator.php')) {
                echo '<p>✅ Plugin Niche Creator está activo</p>';
            } else {
                echo '<p>⚠️ Plugin Niche Creator no está activo. Este plugin complementa al plugin principal.</p>';
            }
        } else {
            echo '<p>⚠️ No se puede verificar si Niche Creator está activo (función is_plugin_active no disponible)</p>';
            
            // Intentar cargar la función
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            
            if (function_exists('is_plugin_active')) {
                if (is_plugin_active('niche-creator/niche-creator.php')) {
                    echo '<p>✅ Plugin Niche Creator está activo (verificado después de cargar funciones)</p>';
                } else {
                    echo '<p>⚠️ Plugin Niche Creator no está activo (verificado después de cargar funciones)</p>';
                }
            }
        }
        ?>
    </div>
    
    <div class="card">
        <h2>Verificación de tablas de Action Scheduler</h2>
        <?php 
        global $wpdb;
        if (isset($wpdb)) {
            $tablas = [
                $wpdb->prefix . 'actionscheduler_actions',
                $wpdb->prefix . 'actionscheduler_groups',
                $wpdb->prefix . 'actionscheduler_logs'
            ];
            
            foreach ($tablas as $tabla) {
                $existe = $wpdb->get_var("SHOW TABLES LIKE '$tabla'");
                if ($existe) {
                    echo "<p>✅ Tabla $tabla existe</p>";
                } else {
                    echo "<p>❌ Tabla $tabla no existe</p>";
                }
            }
        } else {
            echo '<p>⚠️ No se puede acceder a la base de datos de WordPress</p>';
        }
        ?>
    </div>
    
    <div class="card">
        <h2>Verificación de clases</h2>
        <?php
        $clases = [
            'Niche_Creator_Plus' => NICHE_CREATOR_PLUS_PATH . 'includes/class-niche-creator-plus.php',
            'Niche_Creator_Plus_Loader' => NICHE_CREATOR_PLUS_PATH . 'includes/class-niche-creator-plus-loader.php',
            'Niche_Creator_Plus_Admin' => NICHE_CREATOR_PLUS_PATH . 'admin/class-niche-creator-plus-admin.php',
            'Niche_Creator_Plus_Admin_Menu' => NICHE_CREATOR_PLUS_PATH . 'includes/class-admin-menu.php',
            'Niche_Creator_Plus_Reprocessor' => NICHE_CREATOR_PLUS_PATH . 'includes/class-reprocessor.php'
        ];
        
        foreach ($clases as $clase => $archivo) {
            if (file_exists($archivo)) {
                echo "<p>✅ Archivo $archivo existe</p>";
                
                if (class_exists($clase)) {
                    echo "<p>✅ Clase $clase está disponible</p>";
                } else {
                    echo "<p>❌ Clase $clase no está disponible aunque el archivo existe</p>";
                }
            } else {
                echo "<p>❌ Archivo $archivo no existe</p>";
            }
        }
        ?>
    </div>
</div>
