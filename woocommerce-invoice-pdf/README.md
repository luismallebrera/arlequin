# WooCommerce Invoice PDF

Plugin de WordPress/WooCommerce que genera facturas en PDF con todos los metadatos del pedido y las imágenes de los productos.

## Características

- ✅ Genera facturas en PDF profesionales
- ✅ Incluye la imagen de cada producto
- ✅ Muestra todos los metadatos del pedido y productos
- ✅ Información de facturación y envío
- ✅ Botón de descarga en el panel de administración
- ✅ Botón de descarga en "Mi cuenta" para clientes
- ✅ Adjunta automáticamente la factura en el email de pedido completado
- ✅ Compatible con variaciones y atributos de productos
- ✅ Muestra SKU, cantidades, precios y totales
- ✅ Incluye método de pago y notas del cliente

## Requisitos

- WordPress 5.8 o superior
- WooCommerce 5.0 o superior
- PHP 7.4 o superior
- Librería TCPDF (incluida)

## Instalación

1. Descarga o clona este repositorio en la carpeta `wp-content/plugins/`
2. Descarga la librería TCPDF desde [https://tcpdf.org/](https://tcpdf.org/) o instálala vía Composer
3. Extrae TCPDF en la carpeta `lib/tcpdf/` dentro del plugin
4. Activa el plugin desde el panel de WordPress

### Instalación de TCPDF vía Composer

```bash
cd woocommerce-invoice-pdf
composer require tecnickcom/tcpdf
```

Luego crea un enlace simbólico o copia la librería:

```bash
mkdir -p lib/tcpdf
cp -r vendor/tecnickcom/tcpdf/* lib/tcpdf/
```

## Uso

### Desde el panel de administración

1. Ve a **WooCommerce > Pedidos**
2. Busca el pedido del que quieres generar la factura
3. Haz clic en el icono de "Descargar factura"
4. El PDF se descargará automáticamente

### Desde "Mi cuenta" (clientes)

1. Los clientes pueden ir a **Mi cuenta > Pedidos**
2. Hacer clic en "Descargar factura" en cualquier pedido
3. El PDF se descargará automáticamente

### Email automático

El plugin adjunta automáticamente la factura en el email de "Pedido completado" que se envía al cliente.

## Estructura del PDF

La factura incluye:

### Cabecera
- Logo de la tienda (si está configurado)
- Número de factura
- Fecha del pedido
- Estado del pedido

### Direcciones
- Dirección de facturación completa
- Email y teléfono del cliente
- Dirección de envío

### Productos
Para cada producto muestra:
- Imagen del producto (miniatura)
- Nombre del producto
- SKU (si existe)
- Todos los metadatos (variaciones, atributos personalizados, etc.)
- Cantidad
- Precio unitario
- Total por línea

### Totales
- Subtotal
- Gastos de envío
- Impuestos
- Descuentos aplicados
- Total final

### Información adicional
- Método de pago
- Notas del cliente
- Todos los metadatos del pedido

## Personalización

### Modificar el diseño del PDF

Puedes personalizar el diseño editando el archivo:
```
includes/class-pdf-generator.php
```

El método `generate_invoice_html()` contiene todo el HTML que se renderiza en el PDF.

### Añadir tu propio logo

1. Ve a **Apariencia > Personalizar > Identidad del sitio**
2. Sube tu logo
3. El plugin lo usará automáticamente en las facturas

### Filtros disponibles

Puedes usar estos filtros para personalizar el comportamiento:

```php
// Modificar el HTML de la factura
add_filter('wc_invoice_pdf_html', function($html, $order) {
    // Tu código aquí
    return $html;
}, 10, 2);

// Cambiar el nombre del archivo PDF
add_filter('wc_invoice_pdf_filename', function($filename, $order) {
    return 'mi-factura-' . $order->get_order_number() . '.pdf';
}, 10, 2);
```

## Metadatos incluidos

El plugin incluye automáticamente:

### Metadatos de producto
- Variaciones (talla, color, etc.)
- Atributos personalizados
- Campos añadidos por otros plugins
- SKU
- Cualquier meta que no comience por `_`

### Metadatos de pedido
- Información de envío personalizada
- Campos personalizados del checkout
- Datos de plugins de terceros
- Cualquier meta que no comience por `_`

## Compatibilidad

Compatible con:
- WooCommerce Subscriptions
- WooCommerce Product Add-Ons
- WooCommerce Variation Swatches
- YITH WooCommerce plugins
- Y la mayoría de plugins de WooCommerce

## Soporte

Para reportar problemas o sugerir mejoras, abre un issue en el repositorio de GitHub.

## Licencia

GPL v2 o posterior

## Autor

Luis Mallebrera
[https://github.com/luismallebrera](https://github.com/luismallebrera)

## Changelog

### 1.0.0
- Versión inicial
- Generación de PDF con TCPDF
- Inclusión de imágenes de productos
- Todos los metadatos incluidos
- Botones de descarga en admin y frontend
- Adjunto automático en emails
