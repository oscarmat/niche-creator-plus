# Requisitos: Panel de reprocesamiento "Niche Creator Plus"

Este documento describe el propósito, estructura y funcionamiento del módulo de administración personalizado llamado **Niche Creator Plus**, diseñado para reprocesar acciones fallidas gestionadas por Action Scheduler.

---

## 🎯 Objetivo

Este proyecto es un pluging para wordpress, "Niche Creator Plus".

Se trata de un plugin para uso propio, solo en sitios Wordpress de mi propiedad, y como complemento del plug in comercial "Niche Creator". Su objetivo es facilitar determinadas acciones no cubiertas por dicho plugin. Las que están enumeradas aquí pueden ir aumentando en un futuro.

Por el momento, tiene una única opción de submenú, para reprocesar errores en los procesos en fondo del otro plugin. Esta funcionalidad permite:
- Auditar errores programados de hooks relacionados con Niche Creator.
- Reprogramar eventos fallidos espaciados en el tiempo.
- Cancelar los eventos fallidos originales y registrar el cambio.

---

## 🧩 Hooks gestionados

Actualmente se gestionan los siguientes hooks:
- `crear_duplicados`
- `cargar_imagenesia`
- `nichecreator_cargageografica`
- `cargar_geograficos`
- `cargar_scrape`
- `cargar_ia`

---

## ⚙️ Lógica de reprocesado

- Solo se reprocesan acciones con `status = failed`.
- Cada evento fallido se reprocesa mediante `as_schedule_single_action()` con un **espaciado de 120 segundos** respecto al anterior.
- Todas las nuevas acciones se agrupan bajo el slug de grupo: `niche_creator_plus`.
- Las acciones originales se marcan como `canceled` y se añade una entrada de log explicando el cambio.
- Se busca el último evento `pending` ya programado en ese grupo para continuar desde su horario (si existe).

---

## 📊 Panel de administración

El panel incluye:
- Un menú principal llamado **Niche Creator Plus**, insertado **justo después del menú Niche Creator**.
- Un submenú **“Reprocesar eventos”** con:
  - Resumen agrupado de errores por hook y grupo.
  - Botones individuales para reprocesar por hook.
  - Impresión en pantalla de argumentos reprogramados.

---

## 🧪 Comprobaciones de carga

- Si el menú `'nichecreator_creator'` (Niche Creator) **no está disponible**, el panel **no se añade**.
- En ese caso, se muestra un aviso administrativo (`.notice-warning`) indicando la dependencia ausente.

---

## 🔐 Permisos

El acceso al panel requiere la capacidad `manage_options`.

---

## 🔁 Extensibilidad futura

El sistema permite añadir fácilmente nuevos hooks, ajustar el espaciado, exportar los datos, automatizar reprocesos o mejorar la trazabilidad.

