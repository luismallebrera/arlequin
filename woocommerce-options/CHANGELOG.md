# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

## [1.3.0] - 2025-12-23

### Añadido
- **Atributos Condicionales**: Nueva funcionalidad para mostrar/ocultar atributos dinámicamente
  - Atributo maestro: DISEÑO ETIQUETA (MOTIVO, VICHY, CORONA)
  - Atributos dependientes: MOTIVO (6 opciones) y COLOR VICHY (5 colores)
  - Transiciones suaves CSS para mejor UX
  - Compatible con productos simples y variables
  - Scripts JavaScript optimizados con manejo de eventos
- **Incremento de Cantidad**: Nuevo campo para definir el paso de incremento/decremento
  - Campo `_quantity_step` en la edición de productos
  - Compatible con productos simples y variables
  - Las variaciones heredan del producto padre si no tienen valor propio
- Documentación técnica completa en `ATRIBUTOS-CONDICIONALES.md`
- Archivos CSS y JS en directorio `assets/`

### Modificado
- Actualizada la descripción del plugin para incluir atributos condicionales
- Mejorado el README con instrucciones de configuración de atributos condicionales
- Optimizado el código de cantidad mínima para incluir soporte de incrementos

## [1.2.0] - 2024

### Añadido
- **Cantidad Mínima por Producto**: Define la cantidad mínima que el cliente debe comprar
  - Campo `_min_quantity` en la edición de productos
  - Compatible con productos simples y variables
  - Las variaciones pueden heredar del producto padre
  - Validación en el frontend

### Modificado
- Mejorado el soporte para variaciones en el campo RELATED
- Optimizaciones en el código JavaScript del Quick Edit

## [1.1.0] - 2024

### Añadido
- **Soporte Quick Edit y Bulk Edit** para el campo RELATED
  - Edición rápida desde la lista de productos
  - Edición masiva con opción de borrar
  - JavaScript para prellenar valores en Quick Edit

### Modificado
- Mejorada la interfaz de usuario en la edición de productos

## [1.0.0] - 2024

### Añadido
- **Productos Relacionados Personalizables**:
  - Opciones en WooCommerce > Ajustes > Productos
  - Relacionar por categorías, etiquetas o ambos
  - Número configurable de productos (1-12)
  - Columnas configurables (1-6)
- **Campo RELATED Personalizado**:
  - Campo de texto en la edición de productos
  - Agrupa productos con la misma etiqueta
  - Opción "Campo personalizado RELATED" en ajustes
- Configuración inicial del plugin
- Verificación de dependencia de WooCommerce
- Textos traducibles preparados

### Seguridad
- Escapado de todas las salidas HTML
- Sanitización de todos los inputs
- Verificación de permisos en guardado
- Protección contra acceso directo
