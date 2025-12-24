# Guía de Instalación - WooCommerce Invoice PDF

## Requisitos previos

Antes de instalar el plugin, asegúrate de tener:

- WordPress 5.8 o superior instalado
- WooCommerce 5.0 o superior activado
- PHP 7.4 o superior
- Acceso al panel de administración de WordPress
- Acceso FTP/SFTP o al administrador de archivos del hosting

## Paso 1: Descargar la librería TCPDF

El plugin requiere la librería TCPDF para generar los PDFs. Hay dos formas de instalarla:

### Opción A: Descarga manual

1. Ve a [https://github.com/tecnickcom/TCPDF/releases](https://github.com/tecnickcom/TCPDF/releases)
2. Descarga la última versión (archivo .zip)
3. Extrae el archivo descargado

### Opción B: Usando Composer (recomendado)

```bash
cd /ruta/a/wordpress/wp-content/plugins/woocommerce-invoice-pdf
composer require tecnickcom/tcpdf
```

## Paso 2: Estructura de archivos

Asegúrate de que la estructura de archivos quede así:

```
wp-content/
└── plugins/
    └── woocommerce-invoice-pdf/
        ├── woocommerce-invoice-pdf.php
        ├── README.md
        ├── INSTALLATION.md
        ├── includes/
        │   └── class-pdf-generator.php
        ├── assets/
        │   └── css/
        │       └── admin.css
        └── lib/
            └── tcpdf/
                ├── tcpdf.php
                ├── config/
                ├── fonts/
                └── ...
```

## Paso 3: Copiar TCPDF al plugin

### Si descargaste manualmente:

1. Crea la carpeta `lib/tcpdf` dentro de `woocommerce-invoice-pdf`
2. Copia todos los archivos de TCPDF a esta carpeta

```bash
mkdir -p wp-content/plugins/woocommerce-invoice-pdf/lib/tcpdf
cp -r /ruta/a/TCPDF-descomprimido/* wp-content/plugins/woocommerce-invoice-pdf/lib/tcpdf/
```

### Si usaste Composer:

1. Crea la carpeta `lib/tcpdf`
2. Copia desde `vendor/tecnickcom/tcpdf`

```bash
mkdir -p lib/tcpdf
cp -r vendor/tecnickcom/tcpdf/* lib/tcpdf/
```

## Paso 4: Verificar permisos

Asegúrate de que los archivos tengan los permisos correctos:

```bash
# Permisos para carpetas
find woocommerce-invoice-pdf -type d -exec chmod 755 {} \;

# Permisos para archivos
find woocommerce-invoice-pdf -type f -exec chmod 644 {} \;
```

## Paso 5: Activar el plugin

1. Accede al panel de administración de WordPress
2. Ve a **Plugins > Plugins instalados**
3. Busca "WooCommerce Invoice PDF"
4. Haz clic en **Activar**

## Paso 6: Verificar la instalación

### Verificar que WooCommerce está activo

Si ves un mensaje de error diciendo que WooCommerce no está instalado:

1. Ve a **Plugins > Plugins instalados**
2. Activa WooCommerce primero
3. Luego activa WooCommerce Invoice PDF

### Verificar que TCPDF está correctamente instalado

1. Ve a **WooCommerce > Pedidos**
2. Abre cualquier pedido
3. Busca el botón/icono de "Descargar factura"
4. Haz clic en él
5. Si se descarga un PDF, ¡todo está funcionando!

## Solución de problemas

### Error: "Class 'TCPDF' not found"

**Causa:** La librería TCPDF no está correctamente instalada.

**Solución:**
1. Verifica que existe el archivo `lib/tcpdf/tcpdf.php`
2. Verifica que la ruta es correcta en `class-pdf-generator.php`
3. Reinstala TCPDF siguiendo el Paso 2

### Error: "Permission denied"

**Causa:** Los permisos de archivos no son correctos.

**Solución:**
```bash
chmod 755 woocommerce-invoice-pdf
chmod 644 woocommerce-invoice-pdf/*.php
```

### No aparece el botón de descarga

**Causa:** Puede que el plugin no esté activado correctamente.

**Solución:**
1. Desactiva el plugin
2. Reactívalo
3. Limpia la caché del navegador
4. Recarga la página de pedidos

### El PDF se descarga vacío o corrupto

**Causa:** Puede haber un problema con las imágenes o metadatos.

**Solución:**
1. Verifica que las imágenes de productos existen
2. Comprueba los logs de PHP en busca de errores
3. Aumenta el límite de memoria de PHP si es necesario:

```php
// En wp-config.php
define('WP_MEMORY_LIMIT', '256M');
```

### Error de memoria PHP

**Causa:** El límite de memoria de PHP es demasiado bajo.

**Solución:**

Añade esto a tu `wp-config.php`:
```php
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

O contacta con tu hosting para aumentar el límite.

## Configuración adicional (opcional)

### Personalizar el logo

1. Ve a **Apariencia > Personalizar**
2. Haz clic en **Identidad del sitio**
3. Sube tu logo
4. El plugin lo usará automáticamente en las facturas

### Configurar emails

El plugin adjunta automáticamente la factura al email de "Pedido completado". Si quieres cambiar esto:

1. Edita `woocommerce-invoice-pdf.php`
2. Busca la función `attach_invoice_to_email`
3. Modifica el filtro según tus necesidades

## Actualización

Para actualizar el plugin:

1. Haz una copia de seguridad de la carpeta del plugin
2. Reemplaza los archivos (excepto `lib/tcpdf`)
3. Mantén intacta la carpeta `lib/tcpdf`
4. Limpia cualquier caché

## Desinstalación

Para desinstalar el plugin:

1. Desactiva el plugin desde **Plugins > Plugins instalados**
2. Haz clic en **Eliminar**
3. O elimina manualmente la carpeta `wp-content/plugins/woocommerce-invoice-pdf`

## Soporte

Si tienes problemas durante la instalación:

1. Verifica que cumples todos los requisitos
2. Revisa los logs de PHP en busca de errores
3. Abre un issue en GitHub con:
   - Versión de WordPress
   - Versión de WooCommerce
   - Versión de PHP
   - Mensaje de error completo
   - Pasos que seguiste

## Recursos adicionales

- [Documentación de TCPDF](https://tcpdf.org/docs/)
- [Documentación de WooCommerce](https://woocommerce.com/documentation/)
- [Codex de WordPress](https://codex.wordpress.org/)
