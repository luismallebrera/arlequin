# Database Meta Keys Cleanup Scripts

Scripts para analizar y eliminar meta keys no utilizadas en la base de datos de WooCommerce.

## ⚠️ IMPORTANTE - LEE ANTES DE USAR

**SIEMPRE haz un backup completo de tu base de datos antes de ejecutar cualquier script de eliminación.**

## Scripts Disponibles

### 1. `analyze_unused_meta_keys.php`
Analiza la base de datos y muestra qué meta keys están en uso y cuáles no.

**Uso:**
```bash
php analyze_unused_meta_keys.php
```

**Salida:**
- Lista de meta keys que están en uso
- Lista de meta keys no utilizadas (candidatas para eliminación)
- Meta keys antiguas de WooCommerce Jetpack
- Estadísticas de uso por campo

### 2. `backup_meta_keys.php`
Crea un backup en formato SQL de todas las meta keys que serían eliminadas.

**Uso:**
```bash
php backup_meta_keys.php
```

**Salida:**
- Archivo SQL con el backup: `meta_keys_backup_YYYY-MM-DD_HHMMSS.sql`
- El archivo puede usarse para restaurar los datos si es necesario

### 3. `delete_unused_meta_keys.php`
Elimina las meta keys no utilizadas de la base de datos.

**Uso:**

Modo simulación (no elimina nada, solo muestra qué haría):
```bash
php delete_unused_meta_keys.php --dry-run
```

Modo real (elimina las meta keys):
```bash
php delete_unused_meta_keys.php --confirm
```

## Proceso Recomendado

### Paso 1: Analizar
```bash
php analyze_unused_meta_keys.php
```
Revisa el output y verifica qué meta keys no están en uso.

### Paso 2: Backup de la Base de Datos
```bash
# Opción 1: Usando WP-CLI
wp db export backup_before_cleanup.sql

# Opción 2: Usando mysqldump
mysqldump -u usuario -p nombre_base_datos > backup_before_cleanup.sql

# Opción 3: Usar el script de backup específico de meta keys
php backup_meta_keys.php
```

### Paso 3: Simulación
```bash
php delete_unused_meta_keys.php --dry-run
```
Revisa cuidadosamente la lista de meta keys que se eliminarían.

### Paso 4: Eliminación Real
```bash
php delete_unused_meta_keys.php --confirm
```

### Paso 5: Optimizar la Base de Datos
```bash
# Usando WP-CLI
wp db optimize

# O directamente en MySQL
mysql -u usuario -p
USE nombre_base_datos;
OPTIMIZE TABLE wp_postmeta;
```

## Meta Keys que se MANTIENEN

Los scripts mantienen automáticamente estas meta keys:

### Plugin Custom Product Addons:
- `_enable_custom_addons`
- `_enable_text_field_nombre_persona`
- `_enable_text_field_inicial`
- `_enable_text_field_fecha_evento`
- `_enable_text_field_hora_evento`
- `_enable_text_field_localidad`
- `_enable_text_field_iglesia`
- `_enable_text_field_hora_misa`
- `_enable_text_field_restaurante`
- `_enable_text_field_ano_curso`
- `_enable_text_field_frase_texto`
- `_enable_text_field_numero_cuenta`
- `_enable_text_field_menu`
- `_enable_text_field_observaciones`
- `_text_field_option_nombre_persona`
- `_text_field_option_fecha_evento`
- `_text_field_option_inicial`

### Plugin WooCommerce Options:
- `_custom_related_field`

### WooCommerce Jetpack:
- `_wcj_product_input_fields`

### Meta Keys Estándar de WooCommerce:
Todas las meta keys estándar de WooCommerce se mantienen (_sku, _price, _stock, etc.)

## Restaurar desde Backup

Si necesitas restaurar las meta keys eliminadas:

### Desde el backup SQL específico:
```bash
mysql -u usuario -p nombre_base_datos < meta_keys_backup_YYYY-MM-DD_HHMMSS.sql
```

### Desde backup completo:
```bash
# Usando WP-CLI
wp db import backup_before_cleanup.sql

# O usando MySQL
mysql -u usuario -p nombre_base_datos < backup_before_cleanup.sql
```

## Notas de Seguridad

1. **Siempre** ejecuta primero con `--dry-run`
2. **Siempre** haz backup antes de usar `--confirm`
3. Verifica que el sitio funcione correctamente después de la eliminación
4. Guarda los backups en un lugar seguro
5. Si tienes dudas sobre alguna meta key, NO la elimines

## Personalización

Si necesitas mantener meta keys adicionales, edita el archivo `delete_unused_meta_keys.php` y añádelas al array `$used_meta_keys`.

## Troubleshooting

### "WordPress not found"
Ajusta la ruta a `wp-load.php` en cada script:
```php
require_once( '/ruta/correcta/a/wp-load.php' );
```

### Error de permisos
Asegúrate de tener permisos de escritura en el directorio para crear backups.

### Meta key necesaria fue eliminada
Restaura desde el backup SQL:
```bash
mysql -u usuario -p nombre_base_datos < meta_keys_backup_YYYY-MM-DD_HHMMSS.sql
```

## Soporte

Si encuentras problemas o tienes dudas, revisa el código de los scripts antes de ejecutarlos.
