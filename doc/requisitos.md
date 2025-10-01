# Niche Creator Plus - Plugin para WordPress

Este documento describe los requisitos, estructura y funcionamiento del **plugin de WordPress "Niche Creator Plus"**, diseñado específicamente como complemento del plugin comercial "Niche Creator" para sitios WordPress.

---

## 🎯 Objetivo y Alcance

**Niche Creator Plus** es un **plugin para WordPress** desarrollado para uso exclusivo en sitios WordPress propios, como complemento especializado del plugin comercial "Niche Creator". Su objetivo es proporcionar funcionalidades adicionales no cubiertas por el plugin principal.

### Funcionalidades actuales:
- **Panel de reprocesamiento de acciones fallidas** gestionadas por Action Scheduler
- **Interfaz de administración integrada** en el dashboard de WordPress
- **Gestión exclusiva de hooks específicos** relacionados con Niche Creator

---

## 🏗️ Arquitectura técnica

### Plataforma objetivo:
- **WordPress 5.0 o superior**
- **PHP 7.2 o superior**
- **Requiere Action Scheduler** (incluido en Niche Creator)
- **Requiere plugin Niche Creator activo**

### Integración con WordPress:
- Se registra como plugin independiente en el ecosistema de WordPress
- Utiliza hooks y filtros estándar de WordPress (`admin_menu`, `admin_enqueue_scripts`)
- Implementa interfaz de administración personalizada
- Compatible con el sistema de permisos de WordPress (`manage_options`)

---

## 🧩 Hooks gestionados actualmente

El plugin gestiona específicamente los siguientes hooks de Action Scheduler:
- `crear_duplicados`
- `cargar_imagenesia`
- `nichecreator_cargageografica`
- `cargar_geograficos`
- `cargar_scrape`
- `cargar_ia`

---

## ⚙️ Lógica de reprocesado

### Funcionamiento técnico:
- **Filtrado exclusivo**: Solo procesa acciones con `status = 'failed'`
- **Reprogramación inteligente**: Usa `as_schedule_single_action()` con espaciado de **120 segundos**
- **Agrupación consistente**: Todas las acciones se agrupan bajo el slug `niche_creator_plus`
- **Limpieza automática**: Las acciones originales se marcan como `canceled`
- **Registro detallado**: Se añade entrada de log explicando cada reprogramación
- **Continuidad temporal**: Busca el último evento `pending` para mantener la secuencia cronológica

---

## 📊 Panel de administración de WordPress

### Integración en el dashboard:
- **Menú principal**: "Niche Creator Plus" posicionado inmediatamente después del menú "Niche Creator"
- **Submenú específico**: "Reprocesar eventos" con funcionalidad completa
- **Diseño WordPress**: Utiliza estilos y componentes estándar del admin de WordPress

### Características del panel:
- ✅ Resumen agrupado de errores por hook y grupo
- ✅ Botones individuales para reprocesar por hook específico
- ✅ Visualización detallada de argumentos reprogramados
- ✅ Integración completa con la interfaz administrativa de WordPress

---

## 🧪 Comprobaciones de dependencias

### Verificaciones automáticas:
- **Detección de menú padre**: Si `'nichecreator_creator'` (Niche Creator) no está disponible, el menú **no se registra**
- **Mensaje de advertencia**: Se muestra notificación `.notice-warning` indicando la dependencia faltante
- **Verificación de funciones**: Comprueba disponibilidad de `is_plugin_active()` y `as_schedule_single_action()`
- **Validación de archivos**: Confirma existencia de clases requeridas antes de la activación

---

## 🔐 Sistema de permisos

- **Nivel de acceso requerido**: `manage_options` (administrador)
- **Integración estándar**: Utiliza el sistema de capacidades de WordPress
- **Seguridad CSRF**: Implementa nonces en formularios (`nichecreator_plus_accion`, `nichecreator_plus_nonce`)
- **Validación estricta**: Sanitiza todas las entradas de usuario con funciones de WordPress

---

## 🔁 Extensibilidad futura

El diseño del plugin permite:
- ➕ Añadir nuevos hooks fácilmente
- ⚡ Ajustar parámetros de espaciado
- 📤 Exportar datos de errores
- 🤖 Automatizar reprocesos
- 📝 Mejorar trazabilidad y logs
- 🔧 Extender funcionalidades administrativas

---

## 📝 Notas técnicas

- **Código PHP estándar**: Compatible con estándares de WordPress
- **Internacionalización**: Preparado para traducción con archivos `.po/.mo`
- **Estructura de clases**: Sigue patrones de diseño de WordPress
- **Gestión de errores**: Implementa logging detallado con `error_log()`
- **Activación segura**: Verificaciones exhaustivas antes de activar funcionalidades

