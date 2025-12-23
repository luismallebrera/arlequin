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
- **Incremento de cantidad**: Define el paso de incremento/decremento (ej: de 5 en 5)
- **Compatible con productos simples y variables**: Funciona en todos los tipos de producto
- **Hereda de producto padre**: Las variaciones pueden heredar la cantidad mínima del producto variable

### Atributos Condicionales
- **Muestra/oculta atributos dinámicamente**: Los atributos se muestran según la selección del usuario
- **Configuración para DISEÑO ETIQUETA**: 
  - Selecciona "MOTIVO" → Muestra el atributo "MOTIVO" con 6 opciones
  - Selecciona "VICHY" → Muestra el atributo "COLOR VICHY" con 5 colores
  - Selecciona "CORONA" → No muestra atributos adicionales
- **Transiciones suaves**: Animaciones CSS para mejor experiencia de usuario
- **Compatible con productos variables**: Funciona correctamente con variaciones

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
- **Incremento de Cantidad**: Define en cuántas unidades aumenta/disminuye cada clic (ej: 5 para incrementos de 5 en 5)

### Atributos Condicionales

Para usar los atributos condicionales, debes crear los siguientes atributos en **Productos > Atributos**:

1. **DISEÑO ETIQUETA** (slug: `diseno-etiqueta`) - Atributo maestro con 3 términos:
   - MOTIVO
   - VICHY
   - CORONA

2. **MOTIVO** (slug: `motivo`) - Atributo dependiente con 6 términos (tus motivos específicos)

3. **COLOR VICHY** (slug: `color-vichy`) - Atributo dependiente con 5 términos (tus colores)

**Funcionamiento**:
- Cuando el cliente selecciona "MOTIVO" en DISEÑO ETIQUETA → Se muestra el atributo MOTIVO
- Cuando el cliente selecciona "VICHY" → Se muestra el atributo COLOR VICHY
- Cuando el cliente selecciona "CORONA" → No se muestran atributos adicionales

Los atributos se muestran y ocultan automáticamente con transiciones suaves.

**Nota**: Los slugs deben coincidir exactamente con los nombres en el código JavaScript. Si usas slugs diferentes, edita el archivo `assets/js/conditional-attributes.js`.

## Instalación

1. Sube la carpeta `woocommerce-options` a `/wp-content/plugins/`
2. Activa el plugin desde el menú 'Plugins' en WordPress
3. Configura las opciones en WooCommerce > Ajustes > Productos

## Requisitos

- WordPress 5.8 o superior
- WooCommerce 5.0 o superior
- PHP 7.4 o superior
