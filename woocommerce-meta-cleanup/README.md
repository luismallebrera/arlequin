# WooCommerce Meta Keys Cleanup

Plugin de WordPress para analizar y limpiar meta keys no utilizadas en productos de WooCommerce.

## Características

✨ **Análisis Completo**
- Detecta automáticamente meta keys no utilizadas
- Muestra estadísticas detalladas
- Identifica meta keys protegidas

🛡️ **Protección Automática**
- Protege meta keys de Custom Product Addons
- Protege meta keys de WooCommerce estándar
- Protege meta keys de plugins personalizados

💾 **Backup Integrado**
- Crea backups SQL antes de eliminar
- Fácil restauración si es necesario
- Se guarda en wp-content/uploads/

🗑️ **Limpieza Segura**
- Confirmación doble antes de eliminar
- Solo elimina meta keys no utilizadas
- Optimiza la tabla después de limpiar

## Instalación

1. Copia la carpeta `woocommerce-meta-cleanup` a `/wp-content/plugins/`
2. Activa el plugin desde el panel de WordPress
3. Ve a **WooCommerce > Meta Cleanup**

## Uso

### 1. Analizar
1. Ve a la pestaña "Analizar"
2. Haz clic en "Analizar Base de Datos"
3. Revisa los resultados

### 2. Crear Backup
1. Ve a la pestaña "Limpieza"
2. Haz clic en "Crear Backup"
3. Guarda la ubicación del archivo

### 3. Limpiar
1. Marca la casilla de confirmación
2. Haz clic en "Eliminar Meta Keys No Utilizadas"
3. Confirma la acción

## Meta Keys Protegidas

El plugin protege automáticamente:

### Custom Product Addons
- `_enable_custom_addons`
- `_enable_text_field_*` (13 campos)
- `_text_field_option_*` (3 campos)

### WooCommerce Options
- `_custom_related_field`

### WooCommerce Min Max Quantities
- `_wcmmq_s_min_quantity`
- `_wcmmq_s_max_quantity`
- `_wcmmq_s_product_step`

### Product Attributes
- Todos los atributos: `attribute_*` (automático)
  - `attribute_color`, `attribute_talla`, etc.

### Elementor
- Todos los meta keys de Elementor: `_elementor*` (automático)
  - `_elementor_data`, `_elementor_edit_mode`, etc.

### WooCommerce Estándar
- Todas las meta keys estándar (_sku, _price, _stock, etc.)

## Seguridad

- ✅ Requiere permisos de `manage_woocommerce`
- ✅ Protección CSRF con nonces
- ✅ Validación de capacidades
- ✅ Confirmación doble antes de eliminar

## Restaurar desde Backup

Si necesitas restaurar:

```bash
mysql -u usuario -p nombre_db < /ruta/al/backup.sql
```

O desde phpMyAdmin:
1. Abre phpMyAdmin
2. Selecciona la base de datos
3. Ve a "Importar"
4. Selecciona el archivo de backup

## Soporte

Si encuentras algún problema o tienes sugerencias, abre un issue en el repositorio.

## Changelog

### 1.0.0
- Versión inicial
- Análisis de meta keys
- Creación de backups
- Eliminación segura
- Interfaz de administración

## Autor

Luis Mallebrera - [GitHub](https://github.com/luismallebrera)

## Licencia

GPL v2 or later
