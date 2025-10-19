
## Manual de estilo para comentarios en php

### 1. Tipos de comentarios en php

PHP admite tres tipos de comentarios:
- **Comentarios de una línea**: `//` o `#`.
- **Comentarios de varias líneas**: `/* */`.
- **Comentarios de documentación**: `/** */`.

### 2. Uso de comentarios de una línea (`//` o `#`)

Utiliza comentarios de una línea para explicar una línea específica o una pequeña sección de código. Este tipo de comentario es útil para anotar detalles rápidos sin interrumpir el flujo de lectura.

#### Ejemplo:

```php
// Incrementa el contador de visitas
$visitas++;
```

**Buenas prácticas**:
- Utiliza `//` en lugar de `#` para una línea, ya que es el estándar preferido.
- Sé conciso y claro; limita estos comentarios a una sola oración breve.

### 3. Uso de comentarios de varias líneas (`/* */`)

Los comentarios de varias líneas son útiles para explicar fragmentos de código más complejos o realizar anotaciones extensas.

#### Ejemplo:

```php
/*
 * Este bloque valida la entrada del usuario.
 * Si no cumple los requisitos, devuelve un mensaje de error.
 */
if (empty($usuario)) {
    echo "El campo usuario no puede estar vacío.";
}
```

**Buenas prácticas**:
- Usa este formato para comentar funciones de varias líneas.
- Formatea las líneas con una sangría para mejorar la legibilidad.
- Usa este tipo de comentario para anotar detalles adicionales, notas o contexto de implementación.

### 4. Uso de comentarios de documentación (`/** */`)

Los comentarios de documentación (también llamados *DocBlocks*) permiten describir funciones, métodos, clases y variables de una manera estándar. Este formato es compatible con herramientas de documentación automática, como **phpDocumentor**.

#### Ejemplo:

```php
/**
 * Calcula el total de la compra aplicando un descuento.
 *
 * @param float $precio Precio inicial sin descuento
 * @param float $descuento Porcentaje de descuento (0.0 - 1.0)
 * @return float Total de la compra después del descuento
 */
function aplicarDescuento($precio, $descuento) {
    return $precio - ($precio * $descuento);
}
```

**Buenas prácticas**:
- Usa `/**` al inicio y `*/` al final.
- Incluye una breve descripción de la función o clase.
- Define los parámetros con `@param`, el valor de retorno con `@return` y los tipos de datos esperados (como `int`, `float`, `string`).
- Para métodos que arrojan excepciones, usa `@throws` para especificar el tipo de excepción.

### 5. Estándares generales para comentarios en php

#### 5.1. Sé claro y conciso
- Los comentarios deben explicar **por qué** se hace algo, no solo **qué** hace el código.
- Escribe comentarios claros y directos, sin ambigüedades.

#### Ejemplo:

```php
// Mal comentario
$total += $impuesto; // Suma impuesto

// Buen comentario
$total += $impuesto; // Añade el impuesto al total de la factura
```

#### 5.2. Usa inglés o español consistentemente
- En un contexto internacional, el inglés es común. Sin embargo, si todos los colaboradores son hispanohablantes, puedes usar el español.
- Lo importante es mantener la coherencia en todo el proyecto.

#### 5.3. Actualiza los comentarios junto con el código
- Asegúrate de que los comentarios reflejan siempre el estado actual del código. Si realizas cambios en el código, actualiza los comentarios correspondientes.

#### Ejemplo de mal comentario actualizado:

```php
$total = calcularTotal($subtotal); // Llama a aplicarDescuento y luego suma impuestos
```

Si `calcularTotal` ya no aplica el descuento, el comentario debe actualizarse para evitar confusiones.

### 6. Comentarios para documentar variables y constantes

- Usa comentarios cortos y descriptivos para aclarar el propósito de variables y constantes.
- Documenta variables que tienen un nombre ambiguo o una lógica compleja.

#### Ejemplo:

```php
$maxIntentos = 5; // Número máximo de intentos permitidos antes de bloquear al usuario
define('IVA', 0.21); // Impuesto sobre el valor añadido (21%)
```

### 7. Comentarios en estructuras de control y bucles

Es buena práctica comentar bucles complejos y estructuras de control, especialmente si incluyen lógica condicional compleja o dependencias importantes.

#### Ejemplo:

```php
// Itera a través de cada pedido del usuario
foreach ($pedidos as $pedido) {
    if ($pedido->estado === 'pendiente') {
        procesarPedido($pedido); // Procesa el pedido si está pendiente
    }
}
```

### 8. Comentarios de sección

Cuando trabajes en archivos largos, es útil dividir el código en secciones con comentarios que marquen el inicio de cada parte. Esto ayuda a organizar y estructurar mejor el archivo.

#### Ejemplo:

```php
// ================================
// Configuración de la aplicación
// ================================

// Código de configuración aquí...

// ================================
// Funciones auxiliares
// ================================

// Código de funciones aquí...
```

### 9. Comentarios temporales (todo y fixme)

A veces es útil anotar tareas pendientes o puntos que necesitan revisión. Utiliza `todo` para tareas futuras y `fixme` para problemas que necesitan corregirse. Estos comentarios son útiles para mantener el seguimiento de mejoras.

#### Ejemplo:

```php
// TODO: Implementar la función de exportación a PDF
// FIXME: Revisar el cálculo de impuestos, puede haber errores para ciertas regiones
```

### 10. Evita el exceso de comentarios

Si el código es claro y los nombres de variables y funciones son descriptivos, es mejor reducir la cantidad de comentarios. A veces, el exceso de comentarios puede hacer el código más difícil de leer y mantener. En lugar de comentarios excesivos, trata de:
- Usar nombres de variables y funciones descriptivos.
- Dividir el código en funciones más pequeñas y bien definidas.

#### Ejemplo:

```php
// Mal código con comentarios excesivos
$val = calcularTotal($productos, $impuesto); // Calcula el total con productos e impuesto
echo $val; // Imprime el valor total

// Código limpio y claro
$totalConImpuesto = calcularTotal($productos, $impuesto);
echo $totalConImpuesto;
```
