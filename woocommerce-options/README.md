# WooCommerce Custom Options

Plugin simple para personalizar opciones de WooCommerce relacionadas con productos relacionados y cantidades mínimas.

## Características

### Productos Relacionados
- **Relacionar por categorías, etiquetas o campo personalizado**: Elige cómo se relacionan los productos
- **Campo RELATED personalizado**: Define grupos de productos relacionados con etiquetas personalizadas
- **Cantidad configurable**: Define cuántos productos relacionados mostrar (1-12)
- **Columnas configurables**: Elige el número de columnas para la cuadrícula (1-6)
- **Soporte Quick Edit y Bulk Edit**: Edita el campo RELATED rápidamente

### Cantidad Mínima
- **Cantidad mínima por producto**: Define la cantidad mínima que debe comprar el cliente
- **Compatible con productos simples y variables**: Funciona en todos los tipos de producto
- **Hereda de producto padre**: Las variaciones pueden heredar la cantidad mínima del producto variable

## Configuración

### Productos Relacionados
Ve a **WooCommerce > Ajustes > Productos** y encontrarás una nueva sección "Productos Relacionados" con las opciones:

1. **Relacionar productos por**:
   - Solo categorías
   - Solo etiquetas
   - Categorías y etiquetas
   - Campo personalizado RELATED
2. **Número de productos** - Por defecto 4
3. **Columnas** - Por defecto 4

### Campo RELATED
En la edición de cada producto (pestaña General), encontrarás:
- **RELATED**: Etiqueta personalizada para agrupar productos relacionados (ej: "verano", "boda", "pack-especial")

### Cantidad Mínima
En la edición de cada producto (pestaña General), encontrarás:
- **Cantidad Mínima**: Define la cantidad mínima de compra (déjalo vacío para 1 por defecto)

## Instalación

1. Sube la carpeta `woocommerce-options` a `/wp-content/plugins/`
2. Activa el plugin desde el menú 'Plugins' en WordPress
3. Configura las opciones en WooCommerce > Ajustes > Productos

## Requisitos

- WordPress 5.8 o superior
- WooCommerce 5.0 o superior
- PHP 7.4 o superior
