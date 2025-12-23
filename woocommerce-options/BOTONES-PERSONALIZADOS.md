# Botones Personalizados de Productos

Guía completa para usar la funcionalidad de botones personalizados en WooCommerce Custom Options.

## ¿Qué hace esta funcionalidad?

Permite añadir uno o más botones personalizados a tus páginas de productos que enlazan a otros productos de tu tienda. Estos botones aparecen **antes del formulario de variaciones** o del botón "Añadir al carrito", en una posición estratégica para captar la atención del cliente.

## Casos de uso

### 1. Enlazar tallas diferentes
Si tienes un producto en talla M, puedes añadir botones que enlacen a las tallas S, L y XL:
- Botón 1: "Ver talla S" → Producto Talla S
- Botón 2: "Ver talla L" → Producto Talla L
- Botón 3: "Ver talla XL" → Producto Talla XL

### 2. Productos complementarios
En una camiseta, puedes sugerir productos que combinan:
- Botón 1: "Combina con pantalón" → Producto pantalón
- Botón 2: "Ver pack completo" → Pack camiseta + pantalón

### 3. Versiones del producto
Si tienes diferentes versiones de un producto:
- Botón 1: "Versión Premium" → Producto Premium
- Botón 2: "Versión Económica" → Producto Básico

### 4. Cross-selling estratégico
- "Ver accesorios para este producto"
- "Personaliza con bordado"
- "Añade funda protectora"

## Configuración

### Paso 1: Editar el producto

1. Ve a **Productos** en el panel de WordPress
2. Edita el producto donde quieres añadir botones
3. En la pestaña **General** (Product Data), busca la sección **"Botones de Productos Personalizados"**

### Paso 2: Añadir botones

1. Haz clic en **"Añadir Botón"**
2. Aparecerá un formulario con dos campos:
   - **Producto de destino**: Campo de búsqueda para seleccionar el producto
   - **Texto del botón**: El texto que verán tus clientes

### Paso 3: Configurar cada botón

**Campo "Producto de destino":**
- Escribe al menos 3 letras del nombre del producto
- Selecciona el producto de la lista desplegable
- El buscador muestra el nombre y el ID del producto

**Campo "Texto del botón":**
- Escribe un texto claro y llamativo
- Ejemplos: "Ver talla grande", "Combina con...", "Pack completo"
- Máximo recomendado: 3-5 palabras para mejor visualización

### Paso 4: Añadir más botones (opcional)

- Puedes añadir tantos botones como necesites
- Cada clic en "Añadir Botón" crea un nuevo conjunto de campos
- Los botones se mostrarán en el orden en que los crees

### Paso 5: Eliminar botones

- Cada botón tiene un botón "Eliminar" en la esquina superior derecha
- Haz clic para quitar un botón no deseado

### Paso 6: Guardar

- Haz clic en **"Actualizar"** o **"Publicar"** para guardar los cambios
- Los botones aparecerán inmediatamente en la página del producto

## Visualización en el frontend

Los botones aparecen justo antes del formulario de producto con:
- **Diseño profesional**: Botones estilizados que combinan con el tema de WooCommerce
- **Efecto hover**: Animación sutil al pasar el ratón
- **Responsive**: Se adaptan a móviles (botones en columna en pantallas pequeñas)
- **Múltiples botones**: Organizados horizontalmente con espaciado adecuado

### Posición de los botones

```
┌─────────────────────────────────┐
│ Imagen del producto             │
├─────────────────────────────────┤
│ Título, precio, descripción     │
├─────────────────────────────────┤
│ ┌──────────┐ ┌──────────┐      │ ← AQUÍ APARECEN LOS BOTONES
│ │ Botón 1  │ │ Botón 2  │      │
│ └──────────┘ └──────────┘      │
├─────────────────────────────────┤
│ Seleccionar variaciones (si hay)│
├─────────────────────────────────┤
│ [Añadir al carrito]             │
└─────────────────────────────────┘
```

## Personalización avanzada

### Clases CSS automáticas

Cada botón recibe automáticamente una clase CSS basada en el texto que configures. Esto te permite personalizar el estilo de cada tipo de botón.

**Formato de la clase:** `wc-button-{texto-sanitizado}`

**Ejemplos:**
- Texto: "Ver talla grande" → Clase: `wc-button-ver-talla-grande`
- Texto: "Combina con este" → Clase: `wc-button-combina-con-este`
- Texto: "Pack completo" → Clase: `wc-button-pack-completo`

### Personalizar estilos por tipo de botón

Los estilos se encuentran en: `assets/css/frontend-buttons.css`

**Ejemplo 1: Botón azul para "Ver talla grande"**

```css
.wc-button-ver-talla-grande {
    background-color: #2196F3;
    color: #ffffff;
    border-color: #2196F3;
}

.wc-button-ver-talla-grande:hover {
    background-color: #1976D2;
    border-color: #1976D2;
}
```

**Ejemplo 2: Botón dorado para productos premium**

```css
.wc-button-version-premium {
    background-color: #FFD700;
    color: #000000;
    border-color: #FFD700;
    font-weight: bold;
}

.wc-button-version-premium:hover {
    background-color: #FFC700;
}
```

**Ejemplo 3: Botón verde para packs y combos**

```css
.wc-button-ver-pack,
.wc-button-combina-con {
    background-color: #4CAF50;
    color: #ffffff;
    border-color: #4CAF50;
}
```

### Modificar estilos CSS

Los estilos se encuentran en: `assets/css/frontend-buttons.css`

Clases CSS disponibles:
- `.wc-custom-product-buttons` - Contenedor de todos los botones
- `.wc-custom-product-button` - Cada botón individual
- `.wc-button-{texto}` - Clase específica según el texto del botón

**Ejemplo: Cambiar color de los botones**

```css
.wc-custom-product-button {
    background-color: #ff6600;
    color: #ffffff;
}

.wc-custom-product-button:hover {
    background-color: #cc5200;
}
```

### Cómo encontrar la clase de tu botón

1. Abre la página del producto en tu navegador
2. Haz clic derecho en el botón → "Inspeccionar elemento"
3. Busca la clase que empieza con `wc-button-`
4. Usa esa clase en tu CSS personalizado

**Ejemplo práctico completo:**

Si configuras un botón con texto "Ver en color rojo", la clase será `wc-button-ver-en-color-rojo`

```css
/* En tu archivo CSS personalizado o en Appearance > Customize > Additional CSS */
.wc-button-ver-en-color-rojo {
    background-color: #e74c3c;
    color: white;
    border: 2px solid #c0392b;
}

.wc-button-ver-en-color-rojo:hover {
    background-color: #c0392b;
    transform: scale(1.05);
}
```

### Modificar comportamiento JavaScript

El script de administración está en: `assets/js/admin-buttons.js`

Funciones principales:
- `addButtonRow()` - Añade un nuevo botón
- `initProductSelects()` - Inicializa los selectores de productos
- `updateNoButtonsMessage()` - Muestra/oculta mensaje cuando no hay botones

## Preguntas frecuentes

### ¿Cuántos botones puedo añadir?
No hay límite técnico, pero se recomienda entre 2-4 botones para no saturar la página.

### ¿Los botones funcionan con productos variables?
Sí, los botones aparecen tanto en productos simples como variables.

### ¿Puedo enlazar al mismo producto pero con parámetros diferentes?
Los botones enlazan a la página del producto. Si necesitas variaciones específicas, considera crear productos separados.

### ¿Se pueden personalizar los colores?
Sí, editando el archivo `assets/css/frontend-buttons.css` o añadiendo CSS personalizado en tu tema.

### ¿Los botones afectan al SEO?
No negativamente. Son enlaces normales que mejoran la navegación interna de tu tienda.

### ¿Funciona con todos los temas de WooCommerce?
Sí, utiliza los hooks estándar de WooCommerce (`woocommerce_before_variations_form` y `woocommerce_before_add_to_cart_form`).

## Solución de problemas

### Los botones no aparecen

1. Verifica que hayas guardado el producto después de añadir botones
2. Comprueba que ambos campos (producto y texto) estén completos
3. Asegúrate de que el producto enlazado esté publicado
4. Limpia la caché del sitio si usas un plugin de caché

### El buscador de productos no funciona

1. Verifica que WooCommerce esté actualizado
2. Comprueba que tengas permisos de administrador
3. Revisa la consola del navegador en busca de errores JavaScript

### Los estilos no se ven bien

1. Limpia la caché del navegador
2. Verifica que no haya conflictos CSS con tu tema
3. Comprueba que el archivo CSS se esté cargando (inspecciona la página)

## Soporte técnico

- **Archivos involucrados**:
  - PHP: `woocommerce-options.php` (líneas relacionadas con `custom_product_buttons`)
  - JS Admin: `assets/js/admin-buttons.js`
  - CSS Frontend: `assets/css/frontend-buttons.css`

- **Hooks utilizados**:
  - `woocommerce_before_variations_form` - Muestra botones antes de variaciones
  - `woocommerce_before_add_to_cart_form` - Muestra botones en productos simples
  - `woocommerce_product_options_general_product_data` - Añade campos en admin
  - `woocommerce_process_product_meta` - Guarda los datos

- **Meta key**: `_custom_product_buttons` (array serializado)

## Ejemplo completo

**Producto**: Camiseta Básica Blanca (Talla M)

**Configuración de botones**:
1. Botón 1:
   - Producto: Camiseta Básica Blanca Talla S
   - Texto: "Ver talla S"

2. Botón 2:
   - Producto: Camiseta Básica Blanca Talla L
   - Texto: "Ver talla L"

3. Botón 3:
   - Producto: Pack 3 Camisetas Básicas
   - Texto: "Ahorra con el pack de 3"

**Resultado**: Tres botones aparecerán en la página del producto, permitiendo a los clientes navegar fácilmente entre tallas o descubrir el pack promocional.
