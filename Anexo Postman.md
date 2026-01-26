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

## 8.1 Obtener token (login)

Carpeta: `05 - Autenticación (JWT)`

**Add Request**

**Nombre:**
POST – login (admin)

**Método:**
POST

**URL:**

```
{{base_url}}/api/token/
```

**Body (JSON):**

```json
{
  "username": "admin",
  "password": "admin"
}
```

---

## 8.2 Guardar token automáticamente

En la pestaña **Tests** de la request de login, añadir:

```javascript
const data = pm.response.json();
pm.environment.set("token", data.access);
```

Pulsa **Send** y comprueba que la variable de entorno `token` se ha rellenado correctamente.

---

## 8.3 Usar token en endpoints protegidos

En cualquier request que requiera autenticación:

* Pestaña **Authorization**
* Type: `Bearer Token`
* Token:

  ```
  {{token}}
  ```

**Comprobación inicial:**

* Con token → acceso permitido
* Sin token → `401 Unauthorized`

---

## 8.4 Probar lectura pública de cursos

### Listado de cursos

**Request**

* Método: `GET`
* URL:

  ```
  {{base_url}}/api/cursos/
  ```

**Resultado esperado:**

* Sin token → `200 OK`
* Con token → `200 OK`

---

### Detalle de un curso

**Request**

* Método: `GET`
* URL:

  ```
  {{base_url}}/api/cursos/1/
  ```

**Resultado esperado:**

* Sin token → `200 OK`
* Con token → `200 OK`

---

## 8.5 Probar creación de cursos (control de instructor)

### Crear curso como instructor

**Request**

* Método: `POST`
* URL:

  ```
  {{base_url}}/api/cursos/
  ```

**Body (JSON):**

```json
{
  "nombre": "Curso de prueba",
  "descripcion": "Curso creado desde Postman"
}
```

**Resultado esperado:**

* Instructor autenticado → `201 Created`
* El curso queda asociado automáticamente al instructor autenticado

---

### Intentar forzar instructor por body

**Body (JSON):**

```json
{
  "nombre": "Curso inválido",
  "descripcion": "Intento de suplantación",
  "instructor": 5
}
```

**Resultado esperado:**

* El campo `instructor` es ignorado o provoca error
* El curso no se crea en nombre de otro instructor

---

## 8.6 Probar permisos de modificación de cursos

### Editar curso propio

**Request**

* Método: `PATCH`
* URL:

  ```
  {{base_url}}/api/cursos/1/
  ```

**Body (JSON):**

```json
{
  "descripcion": "Descripción modificada"
}
```

**Resultado esperado:**

* Instructor propietario → `200 OK`

---

### Editar curso ajeno

**Resultado esperado:**

* Instructor no propietario → `403 Forbidden`

---

### Borrar curso

**Request**

* Método: `DELETE`
* URL:

  ```
  {{base_url}}/api/cursos/1/
  ```

**Resultado esperado:**

* Instructor propietario o admin → `204 No Content`
* Instructor no propietario → `403 Forbidden`

---

## 8.7 Probar creación de inscripciones

### Crear inscripción como estudiante

**Request**

* Método: `POST`
* URL:

  ```
  {{base_url}}/api/inscripciones/
  ```

**Body (JSON):**

```json
{
  "curso": 1
}
```

**Resultado esperado:**

* Estudiante autenticado → `201 Created`
* La inscripción queda asociada automáticamente al estudiante autenticado

---

### Intentar forzar estudiante por body

**Body (JSON):**

```json
{
  "curso": 1,
  "estudiante": 3
}
```

**Resultado esperado:**

* El campo `estudiante` es ignorado o provoca error
* No se permite crear inscripciones en nombre de otro estudiante

---

## 8.8 Probar visibilidad de inscripciones (`get_queryset`)

### Estudiante autenticado

**Request**

* Método: `GET`
* URL:

  ```
  {{base_url}}/api/inscripciones/
  ```

**Resultado esperado:**

* El estudiante solo ve **sus propias inscripciones**

---

### Instructor autenticado

**Resultado esperado:**

* El instructor solo ve inscripciones de **sus cursos**

---

### Acceso directo por ID a inscripción ajena

**Request**

* Método: `GET`
* URL:

  ```
  {{base_url}}/api/inscripciones/10/
  ```

**Resultado esperado:**

* `404 Not Found`

---

## 8.9 Probar permisos sobre inscripciones

### Borrar inscripción propia

**Request**

* Método: `DELETE`
* URL:

  ```
  {{base_url}}/api/inscripciones/3/
  ```

**Resultado esperado:**

* Estudiante propietario → `204 No Content`

---

### Borrar inscripción ajena

**Resultado esperado:**

* Estudiante no propietario → `403 Forbidden`

---

## 8.10 Probar acción de negocio `inscribirse`

**Request**

* Método: `POST`
* URL:

  ```
  {{base_url}}/api/cursos/1/inscribirse/
  ```

**Body:**

```json
{}
```

**Resultados esperados:**

* Estudiante autenticado → `201 Created`
* Usuario no estudiante → `403 Forbidden`
* Inscripción duplicada → `409 Conflict`

---

## 8.11 Comprobaciones finales

El alumno debe poder demostrar en Postman que:

* Sin token → `401 Unauthorized`
* La lectura de cursos es pública
* Solo el instructor propietario puede modificar o borrar un curso
* Un estudiante solo ve y gestiona sus inscripciones
* No se aceptan `instructor` ni `estudiante` por body
* El admin puede acceder a todos los recursos

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

