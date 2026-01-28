#  GUÍA DE USO DE POSTMAN PARA PROBAR LA API REST

*(Ejemplo base: Cursos – Adaptar siempre a tu temática)*

---

## 0. Introducción importante (leer antes de empezar)

Durante el desarrollo de la API REST **no basta con programar**:
es imprescindible **probar correctamente cada endpoint**.

Para ello usaremos **Postman**, que simula el comportamiento de un frontend real.

>  **Regla fundamental**
> En esta guía usamos como ejemplo el recurso **Cursos**,
> pero **cada alumno debe adaptar los endpoints a su temática**:
>
> * Si tu proyecto es una biblioteca → `libros`
> * Si es una tienda → `productos`
> * Si es una liga → `equipos`, `jugadores`, etc.

La forma de usar Postman es **exactamente la misma**.

---

## 1. Configuración inicial de Postman (se hace una sola vez)

### 1.1 Crear una colección del proyecto

1. Abre Postman.

2. En **Collections** pulsa **New Collection**.

3. Nombre:

   ```
   Proyecto API REST – <tu temática>
   ```

   Ejemplos:

   * `Proyecto API REST – Cursos`
   * `Proyecto API REST – Biblioteca`

4. Dentro de la colección, crea las siguientes carpetas. Botón derecho sobre la colección o tres puntos, Add folder:

   ```
   00 - Pruebas básicas
   01 - CRUD Recurso principal
   02 - Relaciones
   03 - Filtros y paginación
   04 - Acciones de negocio
   05 - Autenticación (JWT)
   ```

 **Esto es obligatorio**: en empresa se valora mucho la organización.

---

### 1.2 Crear un entorno (Environment)

1. Arriba a la derecha → **No Environment → Manage Environments**

2. Pulsa **Add**

3. Nombre del entorno:

   ```
   Local Django
   ```

4. Crea estas variables:

| Variable   | Valor                   |
| ---------- | ----------------------- |
| `base_url` | `http://localhost:8000` |
| `api_url`  | `{{base_url}}/api`      |
| `token`    | *(vacía de momento)*    |

5. Guarda (**Save**) y selecciona el entorno **Local Django**.

 A partir de ahora **NO escribas URLs completas**, usa siempre variables.

---

## 2. BLOQUE 1 – Probar GET básicos (listar y detalle)

### Objetivo

Comprobar que la API responde y devuelve JSON.

---

### 2.1 GET – Listar cursos (o tu recurso)

1. Carpeta: `00 - Pruebas básicas`

2. **Add Request**

3. Nombre:

   ```
   GET - lista cursos
   ```

4. Método: **GET**

5. URL:

   ```
   {{api_url}}/cursos/
   ```

    Sustituye `cursos` por tu recurso si es distinto.

6. Pulsa **Send**

 Debes comprobar:

* Status: `200 OK`
* Body: lista JSON (vacía o con datos)

---

### 2.2 GET – Detalle por ID

1. Duplica la petición anterior.
2. Renombra:

   ```
   GET - detalle curso
   ```
3. URL:

   ```
   {{api_url}}/cursos/1/
   ```
4. **Send**

 Resultado esperado:

* `200 OK` si existe
* `404 Not Found` si no existe (esto es correcto)

---

## 3. BLOQUE 2 – POST y validaciones

### Objetivo

Enviar datos en JSON y entender errores 400.

---

### 3.1 POST – Crear un curso

1. Carpeta: `01 - CRUD Recurso principal`
2. **Add Request**
3. Nombre:

   ```
   POST - crear curso
   ```
4. Método: **POST**
5. URL:

   ```
   {{api_url}}/cursos/
   ```
6. Ve a **Body → raw → JSON**
7. Escribe un JSON válido (ajústalo a tu modelo):

```json
{
  "titulo": "Curso Django",
  "precio": 19.99,
  "nivel": "BAS",
  "activo": true
}
```

8. **Send**

 Debes comprobar:

* Status: `201 Created`
* Body: objeto creado con `id`

---

### 3.2 POST – Probar error de validación

1. Duplica la petición anterior.
2. Renombra:

   ```
   POST - crear curso (error)
   ```
3. Usa un JSON incorrecto:

```json
{
  "titulo": "Curso incorrecto",
  "precio": -10
}
```

4. **Send**

 Resultado esperado:

* Status: `400 Bad Request`
* Mensajes de error por campo

 **Esto es obligatorio probarlo**, no solo los casos correctos.

---

## 4. BLOQUE 3 – CRUD completo (PUT, PATCH, DELETE)

### Objetivo

Probar todas las operaciones REST.

---

### 4.1 PATCH – Actualización parcial

1. **Add Request**
2. Nombre:

   ```
   PATCH - actualizar precio
   ```
3. Método: **PATCH**
4. URL:

   ```
   {{api_url}}/cursos/1/
   ```
5. Body:

```json
{
  "precio": 29.99
}
```

6. **Send**

---

### 4.2 PUT – Actualización completa

1. **Add Request**
2. Nombre:

   ```
   PUT - actualizar curso completo
   ```
3. Método: **PUT**
4. URL:

   ```
   {{api_url}}/cursos/1/
   ```
5. Body (todos los campos obligatorios):

```json
{
  "titulo": "Curso Django Avanzado",
  "precio": 25.00,
  "nivel": "MED",
  "activo": true
}
```

---

### 4.3 DELETE – Borrado

1. **Add Request**
2. Nombre:

   ```
   DELETE - borrar curso
   ```
3. Método: **DELETE**
4. URL:

   ```
   {{api_url}}/cursos/1/
   ```
5. **Send**

 Después prueba:

* `GET /cursos/1/` → debe devolver `404`

---

## 5. BLOQUE 4 – Probar relaciones

### Objetivo

Enviar y leer relaciones correctamente.

---

### 5.1 ForeignKey por ID (ej. categoría)

```json
{
  "titulo": "Curso con categoría",
  "precio": 15,
  "categoria": 3
}
```

 **Siempre se envían IDs**, no objetos completos.

---

### 5.2 Leer relaciones en GET

* Observa si el JSON muestra:

  * Solo el ID (`categoria: 3`)
  * O un objeto anidado

Ambas opciones son correctas según diseño.

---

## 6. BLOQUE 5 – Filtros, búsqueda y paginación

### Objetivo

Usar **Params** en Postman.

---

1. **Add Request**
2. Nombre:

   ```
   GET - cursos con filtros
   ```
3. URL:

   ```
   {{api_url}}/cursos/
   ```

### En pestaña **Params**:

| KEY      | VALUE  |
| -------- | ------ |
| activo   | true   |
| search   | django |
| ordering | precio |
| page     | 2      |

Pulsa **Send** y observa cómo cambia la respuesta.

---

## 7. BLOQUE 6 – Acciones de negocio

### Objetivo

Probar endpoints no CRUD.

Ejemplo: inscribirse en un curso.

1. Carpeta: `04 - Acciones de negocio`
2. **Add Request**
3. Nombre:

   ```
   POST - inscribirse en curso
   ```
4. Método: **POST**
5. URL:

   ```
   {{api_url}}/cursos/5/inscribirse/
   ```
6. Body:

```json
{
  "estudiante_id": 2
}
```

---

# 8. BLOQUE 7 – Autenticación, permisos y control de acceso (Postman)
---

## 8.1 Objetivo de este bloque

En este bloque vamos a comprobar **únicamente lo que se ha explicado en clase sobre JWT**:

* Obtener un token con usuario y contraseña.
* Guardar el token en Postman.
* Usar el token en peticiones protegidas.
* Ver claramente la diferencia entre:

  * endpoints públicos
  * endpoints protegidos
* Entender los errores `401` y `403`.

---

## 8.2 Obtener token JWT (login)

Carpeta:
`05 - Autenticación (JWT)`

### Request: Login

**Nombre**

```
POST - login (obtener token)
```

**Método**

```
POST
```

**URL**

```
{{base_url}}/api/token/
```

**Body → raw → JSON**

```json
{
  "username": "admin",
  "password": "admin"
}
```

*(Usa un usuario real creado previamente)*

---

### Resultado esperado

* Status: `200 OK`
* Body contiene:

```json
{
  "refresh": "xxxxx",
  "access": "yyyyy"
}
```

 **El token importante para las peticiones es `access`.**

---

## 8.3 Guardar el token automáticamente en Postman

Para no copiar el token a mano en cada petición:

1. Abre la pestaña **Scripts** de la request de login.
2. Añade este código:

```javascript
const data = pm.response.json();
pm.environment.set("token", data.access);
```

3. Pulsa **Send**.

Comprueba en el entorno **Local Django** que la variable:

```
token
```

ya tiene valor.

---

## 8.4 Probar un endpoint protegido SIN token

### Ejemplo: crear curso sin autenticación

**Request**

* Método: `POST`
* URL:

```
{{api_url}}/cursos/
```

* Body válido en JSON

**NO pongas token**

---

### Resultado esperado

* Status: `401 Unauthorized`

 Esto demuestra que **la API está protegida**.

---

## 8.5 Usar el token en un endpoint protegido

### Cómo enviar el token

En cualquier request protegida:

1. Pestaña **Authorization**
2. Type: `Bearer Token`
3. Token:

```
{{token}}
```

---

### Repetir la creación de curso

Misma request que antes, ahora **con token**.

---

### Resultado esperado

* Status: `201 Created`
* El recurso se crea correctamente.

 **Conclusión clara para el alumno**:

> *Sin token no puedo escribir, con token sí.*

---

## 8.6 Comprobar endpoints públicos

Según los apuntes:

* `list`
* `retrieve`

son públicos.

---

### Listar cursos SIN token

**Request**

* Método: `GET`
* URL:

```
{{api_url}}/cursos/
```

**Resultado esperado**

* `200 OK`
* Devuelve lista de cursos

---

### Detalle de curso SIN token

**Request**

* Método: `GET`
* URL:

```
{{api_url}}/cursos/1/
```

**Resultado esperado**

* `200 OK`

 Esto confirma que **no todo está protegido**, solo lo sensible.

---

## 8.7 Probar una acción de negocio protegida

Ejemplo: `inscribirse`

---

### Inscribirse SIN token

**Request**

* Método: `POST`
* URL:

```
{{api_url}}/cursos/1/inscribirse/
```

**Resultado esperado**

* `401 Unauthorized`

---

### Inscribirse CON token

Misma request, ahora con **Authorization → Bearer {{token}}**

---

### Resultado esperado

* `201 Created`
* Mensaje de éxito

 Se refuerza la idea clave:

> **Las acciones de negocio siempre van protegidas.**

---

## 8.8 Errores HTTP importantes 

El alumno debe ser capaz de provocar y explicar:

### `401 Unauthorized`

* No enviar token
* Token inválido
* Token expirado

---

### `403 Forbidden`

* Usuario autenticado
* Endpoint protegido por permisos
* No autorizado para esa acción

---

## 8.9 Resumen visual del comportamiento esperado

| Endpoint                    | Token | Resultado |
| --------------------------- | ----- | --------- |
| GET /cursos/                | No    | 200 OK    |
| GET /cursos/1/              | No    | 200 OK    |
| POST /cursos/               | No    | 401       |
| POST /cursos/               | Sí    | 201       |
| POST /cursos/1/inscribirse/ | No    | 401       |
| POST /cursos/1/inscribirse/ | Sí    | 201       |

---


## 9. BLOQUE 8 – Flujo profesional completo

### Flujo final recomendado en Postman

1. Login (guardar token)
2. Crear curso
3. Listar cursos
4. Filtrar cursos
5. Actualizar curso
6. Acción de negocio
7. Borrar curso
8. Probar acceso sin token (401)

---

## 10. Checklist final 

El alumno debe poder demostrar en Postman:

* [ ] GET lista y detalle
* [ ] POST con validaciones
* [ ] PUT / PATCH
* [ ] DELETE
* [ ] Relaciones funcionando
* [ ] Filtros y paginación
* [ ] Acción de negocio
* [ ] JWT funcionando correctamente

