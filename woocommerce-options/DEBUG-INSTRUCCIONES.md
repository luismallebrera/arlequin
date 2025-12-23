# Instrucciones de Debug - Botones Personalizados

## Cómo ver los logs de debug

He añadido logs de debug temporales para identificar por qué los botones no se guardan.

### Paso 1: Activar debug en WordPress

Edita el archivo `wp-config.php` de tu WordPress y añade/modifica estas líneas:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

### Paso 2: Probar el guardado

1. Ve a editar un producto
2. Añade un botón con:
   - Producto de destino seleccionado
   - Texto del botón rellenado
3. Haz clic en "Actualizar" o "Publicar"

### Paso 3: Ver los logs

Los logs se guardan en: `wp-content/debug.log`

Busca líneas que contengan:
- `Custom Product Buttons POST data:`
- `Processing button - Product ID:`
- `Buttons to save:`
- `Update result:`

### Qué buscar en los logs

**Si ves "NOT SET":**
```
Custom Product Buttons POST data: NOT SET
```
Significa que los datos no se están enviando desde el formulario.

**Si ves los datos pero Product ID es 0:**
```
Processing button - Product ID: 0, Text: Mi texto
```
Significa que el select de WooCommerce no está guardando el ID del producto correctamente.

**Si todo se ve bien pero dice "FAILED":**
```
Update result: FAILED
```
Hay un problema con la base de datos o permisos.

## Soluciones comunes

### Problema: Los datos no se envían (NOT SET)

**Causa:** El JavaScript no está funcionando o hay un conflicto.

**Solución:**
1. Abre la consola del navegador (F12)
2. Ve a la pestaña "Consola"
3. Busca errores en rojo
4. Comparte los errores que encuentres

### Problema: Product ID es 0

**Causa:** El select2/selectWoo no está inicializado correctamente.

**Solución:**
1. Verifica que WooCommerce esté actualizado
2. Comprueba si hay conflictos con otros plugins
3. Intenta desactivar otros plugins temporalmente

### Problema: Nonce falla

**Causa:** Problemas de caché o sesión.

**Solución:**
1. Limpia la caché del navegador
2. Limpia la caché del sitio (si usas un plugin de caché)
3. Cierra sesión y vuelve a iniciar sesión

## Alternativa manual para probar

Si quieres probar manualmente que el guardado funciona, puedes ejecutar esto en la consola de WordPress (Herramientas > Site Health > Info > Debug):

```php
<?php
$product_id = 123; // ID del producto donde quieres añadir el botón
$buttons = array(
    array(
        'product_id' => 456,  // ID del producto al que enlaza el botón
        'button_text' => 'Ver producto relacionado'
    )
);
update_post_meta( $product_id, '_custom_product_buttons', $buttons );
echo "Guardado: " . print_r( get_post_meta( $product_id, '_custom_product_buttons', true ), true );
?>
```

## Contacto

Envíame:
1. El contenido del archivo `wp-content/debug.log` (las líneas con "Custom Product Buttons")
2. Los errores de la consola del navegador (si los hay)
3. La versión de WordPress y WooCommerce que estás usando
