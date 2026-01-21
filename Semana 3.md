# BLOQUE 4 – Relaciones entre modelos en la API REST

---

## 1. Objetivo del bloque

El objetivo de este bloque es aprender a **exponer relaciones entre modelos** en una API REST desarrollada con Django Rest Framework (DRF), de forma **realista y profesional**, entendiendo:

* Qué tipo de relación existe entre los modelos
* Cómo debe representarse en JSON
* Qué información se devuelve en lectura
* Qué información se envía en escritura
* Qué decisiones de diseño son adecuadas en un proyecto real

Una API sin relaciones correctamente representadas **no es usable** en un entorno profesional.

---

## 2. Tipos de relaciones en Django y su impacto en la API

### 2.1 Relación Uno a Muchos (1:N – `ForeignKey`)

Un recurso pertenece a otro, y este puede tener muchos asociados.

Ejemplos:

* Curso → Categoría
* Libro → Autor
* Producto → Marca

En la base de datos es una clave foránea.
En la API debe decidirse **cómo se representa esa relación**.

---

### 2.2 Relación Muchos a Muchos (N:M – `ManyToManyField`)

Un recurso puede relacionarse con muchos del otro tipo y viceversa.

Ejemplos:

* Curso ↔ Etiquetas
* Película ↔ Actores

Puede ser:

* Simple (sin datos extra)
* Con modelo intermedio (cuando hay información adicional)

---

### 2.3 Relación Muchos a Muchos con datos extra (`through`)

Es el caso más habitual en aplicaciones reales.

Ejemplos:

* Alumno ↔ Curso (fecha de inscripción, nota)
* Pedido ↔ Producto (cantidad, precio)
* Jugador ↔ Partido (minutos, goles)

Aquí **la relación en sí misma es una entidad**.

---

### 2.4 Relación Uno a Uno (1:1 – `OneToOneField`)

Se usa para extender un modelo con información adicional.

Ejemplos:

* Usuario ↔ Perfil
* Usuario ↔ Dirección principal

---

## 3. Concepto clave: lectura y escritura en APIs

En APIs profesionales se distingue claramente entre:

* **Lectura (GET)**: se pueden devolver objetos más completos
* **Escritura (POST / PUT / PATCH)**: se suelen enviar identificadores (IDs)

Esta separación:

* Simplifica la API
* Evita errores de validación
* Reduce complejidad innecesaria

---

## 4. Relación 1:N en DRF: formas habituales de implementación

### 4.1 Representar la relación mediante ID (opción estándar)

Modelo:

```python
class Curso(models.Model):
    titulo = models.CharField(max_length=200)
    categoria = models.ForeignKey(Categoria, on_delete=models.SET_NULL, null=True)
```

Serializer:

```python
class CursoSerializer(serializers.ModelSerializer):
    class Meta:
        model = Curso
        fields = ['id', 'titulo', 'categoria']
```

Representación JSON:

```json
{
  "id": 1,
  "titulo": "Python",
  "categoria": 3
}
```

Esta opción es:

* Ligera
* Escalable
* Muy común en listados

---

### 4.2 Serialización anidada (solo lectura)

Permite devolver más información del recurso relacionado.

```python
class CategoriaSerializer(serializers.ModelSerializer):
    class Meta:
        model = Categoria
        fields = ['id', 'nombre']

class CursoSerializer(serializers.ModelSerializer):
    categoria = CategoriaSerializer(read_only=True)

    class Meta:
        model = Curso
        fields = ['id', 'titulo', 'categoria']
```

Se recomienda:

* Para vistas de detalle
* No para escritura directa

---

### 4.3 Patrón mixto: un campo para escribir y otro para leer

Este patrón es muy profesional y muy utilizado.

```python
class CursoSerializer(serializers.ModelSerializer):
    categoria_detalle = CategoriaSerializer(
        source="categoria",
        read_only=True
    )
    categoria = serializers.PrimaryKeyRelatedField(
        queryset=Categoria.objects.all(),
        allow_null=True,
        required=False
    )

    class Meta:
        model = Curso
        fields = ['id', 'titulo', 'categoria', 'categoria_detalle']
```

Ventajas:

* Escritura sencilla (ID)
* Lectura rica (objeto)
* API clara y mantenible

---

## 5. Relación N:M simple en DRF

Modelo:

```python
class Curso(models.Model):
    etiquetas = models.ManyToManyField(Etiqueta, blank=True)
```

### 5.1 Representación por IDs

```python
class CursoSerializer(serializers.ModelSerializer):
    class Meta:
        model = Curso
        fields = ['id', 'titulo', 'etiquetas']
```

JSON:

```json
{
  "id": 1,
  "titulo": "Python",
  "etiquetas": [2, 4, 7]
}
```

---

### 5.2 Serialización anidada (lectura)

```python
class EtiquetaSerializer(serializers.ModelSerializer):
    class Meta:
        model = Etiqueta
        fields = ['id', 'nombre']

class CursoSerializer(serializers.ModelSerializer):
    etiquetas = EtiquetaSerializer(many=True, read_only=True)

    class Meta:
        model = Curso
        fields = ['id', 'titulo', 'etiquetas']
```

---

## 6. Relación N:M con modelo intermedio (`through`)

Modelo intermedio:

```python
class Inscripcion(models.Model):
    estudiante = models.ForeignKey(
        Estudiante,
        on_delete=models.CASCADE,
        related_name="inscripciones"
    )
    curso = models.ForeignKey(
        Curso,
        on_delete=models.CASCADE,
        related_name="inscripciones"
    )
    fecha_inscripcion = models.DateField(auto_now_add=True)
    nota_final = models.IntegerField(null=True, blank=True)
```

Serializer:

```python
class InscripcionSerializer(serializers.ModelSerializer):
    class Meta:
        model = Inscripcion
        fields = [
            'id',
            'estudiante',
            'curso',
            'fecha_inscripcion',
            'nota_final'
        ]
```

Exposición desde el curso:

```python
class CursoSerializer(serializers.ModelSerializer):
    inscripciones = InscripcionSerializer(many=True, read_only=True)

    class Meta:
        model = Curso
        fields = ['id', 'titulo', 'inscripciones']
```

---

## 7. Relación 1:1 en DRF

Modelo:

```python
class Perfil(models.Model):
    usuario = models.OneToOneField(User, on_delete=models.CASCADE)
    avatar = models.CharField(max_length=255, blank=True)
```

Serializer:

```python
class PerfilSerializer(serializers.ModelSerializer):
    class Meta:
        model = Perfil
        fields = ['avatar']
```

Uso anidado:

```python
class UsuarioSerializer(serializers.ModelSerializer):
    perfil = PerfilSerializer(read_only=True)

    class Meta:
        model = User
        fields = ['id', 'username', 'perfil']
```

---

## 8. Errores comunes que deben evitarse

* Anidar todos los datos en listados
* No usar `related_name`
* Mezclar lectura y escritura sin control
* Intentar crear relaciones complejas sin haberlas diseñado

---

## 9. Práctica

Cada alumno debe:

1. Implementar en su proyecto:

   * Una relación 1:N
   * Una relación N:M
   * Una relación N:M con modelo intermedio
   * Una relación 1:1

2. Exponer al menos una relación usando:

   * Patrón mixto (campo de escritura + campo de lectura)

3. Entregar:

   * `models.py`
   * `serializers.py`
   * Ejemplos de JSON obtenidos según el documento anexo

---

---


# BLOQUE 5 – Filtros, búsqueda, ordenación y paginación

## 1. Objetivo del bloque

El objetivo de este bloque es **hacer usable, eficiente y profesional una API REST**.

Una API que devuelve siempre **todos los datos sin control**:

* No escala.
* Consume más red de la necesaria.
* Es difícil de usar desde frontend o aplicaciones móviles.
* No cumple estándares profesionales.

En este bloque aprenderás a:

* **Filtrar resultados** por campos concretos.
* **Buscar por texto** en uno o varios campos.
* **Ordenar resultados** de forma flexible.
* **Paginar respuestas** para no devolver listados gigantes.

Estos mecanismos **no cambian las rutas**, solo modifican el comportamiento de las peticiones mediante **query parameters**.

---

## 2. Query parameters (parámetros de consulta)

### 2.1. Qué son

Los *query parameters* son parámetros que se añaden al final de la URL y permiten **personalizar una petición** sin crear nuevas rutas.

Formato general:

```
/recurso/?parametro=valor
```

Ejemplos habituales:

* `?activo=true` → filtrar
* `?search=python` → búsqueda
* `?ordering=-precio` → ordenación
* `?page=2` → paginación

### 2.2. Ventajas

* Mantienen una API limpia (menos endpoints).
* Son fácilmente combinables.
* Son estándar en REST.
* Son fáciles de usar desde frontend, Postman o navegador.

Ejemplo combinado:

```
/cursos/?activo=true&search=python&ordering=-precio&page=2
```

---

## 3. Filtros por campos con `django-filter`

### 3.1. Qué es `django-filter`

`django-filter` es una librería que permite **convertir automáticamente parámetros de URL en filtros ORM**.

DRF se integra con ella mediante un *filter backend*.

### 3.2. Instalación (OBLIGATORIA para este bloque)

Este bloque **sí requiere instalar una librería nueva**.

```bash
pip install django-filter
```

### 3.3. Activar `django-filter` en el proyecto

#### A) Añadir a `INSTALLED_APPS`

En `settings.py`:

```python
INSTALLED_APPS = [
    ...
    'django_filters',
]
```

#### B) (Recomendado) Configuración global en DRF

En `settings.py`:

```python
REST_FRAMEWORK = {
    'DEFAULT_FILTER_BACKENDS': [
        'django_filters.rest_framework.DjangoFilterBackend',
    ]
}
```

Esto evita tener que repetir el backend en todos los ViewSets (aunque puedes hacerlo a nivel local si lo prefieres).

---

## 4. Filtros simples por campos (`filterset_fields`)

### 4.1. Configuración básica en un ViewSet

En `views.py`:

```python
from django_filters.rest_framework import DjangoFilterBackend

class CursoViewSet(ModelViewSet):
    queryset = Curso.objects.all()
    serializer_class = CursoSerializer
    filter_backends = [DjangoFilterBackend]
    filterset_fields = ['activo', 'nivel', 'categoria']
```

### 4.2. Qué permite este enfoque

Con esta configuración, DRF permite filtrar automáticamente por esos campos usando la URL:

* `/cursos/?activo=true`
* `/cursos/?nivel=basico`
* `/cursos/?categoria=3`

### 4.3. Tipos de campos soportados directamente

Este enfoque es ideal para:

* **Booleanos** (`True / False`)
* **Choices** (campos con opciones limitadas)
* **Claves foráneas** (filtrado por ID)

No es adecuado cuando:

* Necesitas rangos (`precio mínimo / máximo`)
* Fechas
* Comparaciones (`>=`, `<=`, etc.)
* Lógica más compleja

Para eso usamos **FilterSet**.

---

## 5. Filtros avanzados con `FilterSet`

### 5.1. Cuándo usar `FilterSet`

Usa `FilterSet` cuando necesites:

* Rangos numéricos.
* Rangos de fechas.
* Nombres de parámetros personalizados.
* Mayor control sobre validación y comportamiento.

### 5.2. Crear el archivo `filters.py`

En tu app (por ejemplo `cursos/`), crea un nuevo archivo:

```
cursos/
 ├── filters.py
```

Este archivo **no existe por defecto**, lo creas tú para centralizar los filtros avanzados.

### 5.3. Definir un FilterSet

En `filters.py`:

```python
import django_filters
from .models import Curso

class CursoFilter(django_filters.FilterSet):

    precio_min = django_filters.NumberFilter(
        field_name="precio",
        lookup_expr="gte"
    )

    precio_max = django_filters.NumberFilter(
        field_name="precio",
        lookup_expr="lte"
    )

    class Meta:
        model = Curso
        fields = ['activo', 'nivel', 'categoria']
```

### 5.4. Explicación teórica importante

* `field_name="precio"` → campo del modelo.
* `lookup_expr="gte"` → operador ORM (`>=`).
* `lookup_expr="lte"` → operador ORM (`<=`).

Esto genera filtros como:

* `?precio_min=50`
* `?precio_max=200`

Internamente, Django ejecuta:

```python
Curso.objects.filter(precio__gte=50)
```

---

## 6. Enlazar el FilterSet al ViewSet

En `views.py`:

```python
from django_filters.rest_framework import DjangoFilterBackend
from .filters import CursoFilter

class CursoViewSet(ModelViewSet):
    queryset = Curso.objects.all()
    serializer_class = CursoSerializer
    filter_backends = [DjangoFilterBackend]
    filterset_class = CursoFilter
```

### 6.1. Qué cambia respecto a `filterset_fields`

* Ganas control total.
* Puedes combinar filtros simples + avanzados.
* Puedes documentar y mantener los filtros mejor.

---

## 7. Alternativa: filtros avanzados sin `filters.py`

Para casos sencillos, puedes definir filtros avanzados directamente en el ViewSet:

```python
filter_backends = [DjangoFilterBackend]
filterset_fields = {
    "precio": ["gte", "lte"],
}
```

Uso en URL:

* `?precio__gte=50`
* `?precio__lte=200`

### Cuándo usar esta opción

* Proyectos pequeños.
* Filtros puntuales.
* Cuando no quieres crear archivos extra.

### Cuándo NO usarla

* APIs medianas o grandes.
* Cuando los filtros empiezan a crecer.
* Cuando quieres parámetros más legibles (`precio_min`).

---

## 8. Búsqueda textual (`SearchFilter`)

### 8.1. Qué problema resuelve

La búsqueda textual permite encontrar resultados por **texto libre**, sin necesidad de filtros exactos.

Ejemplo:

```
/cursos/?search=python
```

### 8.2. Activar búsqueda en un ViewSet

En `views.py`:

```python
from rest_framework.filters import SearchFilter

class CursoViewSet(ModelViewSet):
    ...
    filter_backends = [DjangoFilterBackend, SearchFilter]
    search_fields = ['titulo', 'descripcion']
```

### 8.3. Características de la búsqueda

* Coincidencias **parciales**.
* **No distingue mayúsculas/minúsculas**.
* Usa `icontains` internamente.
* Ideal para texto descriptivo.

### 8.4. Limitaciones

* No es búsqueda semántica.
* No sustituye a motores tipo Elasticsearch.
* Perfecta para APIs educativas y proyectos medios.

---

## 9. Ordenación de resultados (`OrderingFilter`)

### 9.1. Para qué sirve

Permite al cliente decidir:

* Por qué campo ordenar.
* Si ascendente o descendente.

### 9.2. Configuración en el ViewSet

```python
from rest_framework.filters import OrderingFilter

class CursoViewSet(ModelViewSet):
    ...
    filter_backends = [
        DjangoFilterBackend,
        SearchFilter,
        OrderingFilter
    ]
    ordering_fields = ['precio', 'fecha_inicio']
    ordering = ['fecha_inicio']
```

### 9.3. Uso desde la URL

* `?ordering=precio` → ascendente
* `?ordering=-precio` → descendente
* `?ordering=fecha_inicio`

### 9.4. Buenas prácticas

* Limita `ordering_fields` (no expongas todos).
* Define un orden por defecto (`ordering`).
* Evita ordenar por campos no indexados en grandes volúmenes.

---

## 10. Paginación

### 10.1. Por qué es obligatoria

Sin paginación:

* Respuestas enormes.
* Más consumo de red.
* Peor rendimiento.
* Mala experiencia en frontend.

### 10.2. Configuración global recomendada

En `settings.py`:

```python
REST_FRAMEWORK = {
    'DEFAULT_PAGINATION_CLASS':
        'rest_framework.pagination.PageNumberPagination',
    'PAGE_SIZE': 5
}
```

### 10.3. Funcionamiento

DRF devolverá respuestas como:

```json
{
  "count": 42,
  "next": "http://.../?page=2",
  "previous": null,
  "results": [
    ...
  ]
}
```

### 10.4. Uso desde la URL

* `?page=1`
* `?page=2`

---

## 11. Combinación de todos los mecanismos

Ejemplo real de URL completa:

```
/cursos/?activo=true&search=python&ordering=-precio&precio_min=50&page=2
```

DRF:

1. Filtra
2. Busca
3. Ordena
4. Pagina

En ese orden.

---

## 12. Práctica del bloque

Cada alumno debe, en **su recurso principal** del proyecto:

### 12.1. Implementar obligatoriamente

* Filtros por campos simples.
* Búsqueda textual.
* Ordenación.
* Paginación.

### 12.2. Implementar al menos un filtro avanzado

Ejemplos válidos:

* Rango de precios.
* Rango de fechas.
* Cantidad mínima/máxima.
* Duración mínima/máxima.

---

---


# BLOQUE 6 – Endpoints de negocio y acciones personalizadas (DRF)

## 1. Objetivo del bloque

En una API “real”, el CRUD (Create, Read, Update, Delete) suele ser solo la base. Las aplicaciones suelen necesitar **procesos**: inscribirse a un curso, confirmar una reserva, finalizar un pedido, publicar un artículo, etc.

En este bloque vas a aprender a:

* Representar **acciones** (no solo recursos) en una API REST.
* Implementarlas con **ViewSets + @action** en Django REST Framework (DRF).
* Validar datos de entrada con **serializadores no ligados a modelos** (Serializer “manual”).
* Devolver respuestas y errores coherentes (códigos HTTP + mensajes).
* Crear **acciones por objeto** (detail=True) y **acciones de colección** (detail=False).

---

## 2. Qué es un endpoint de negocio

### 2.1. CRUD vs negocio

Un endpoint CRUD “habla” de recursos:

* `GET /cursos/` → lista cursos
* `POST /cursos/` → crea curso
* `GET /cursos/{id}/` → detalle
* `PUT/PATCH /cursos/{id}/` → actualiza
* `DELETE /cursos/{id}/` → elimina

Pero un endpoint de negocio “habla” de una **acción** con significado funcional:

* `POST /cursos/{id}/inscribirse/` → “inscribirse en este curso”
* `POST /pedidos/{id}/finalizar/` → “finalizar pedido”
* `POST /reservas/{id}/confirmar/` → “confirmar reserva”
* `POST /productos/{id}/valorar/` → “valorar producto”

### 2.2. Características típicas

Un endpoint de negocio suele:

* Representar una **acción** (un verbo funcional).
* Ejecutar **lógica** (validaciones, reglas, estados…).
* Afectar a **uno o varios modelos** (crear relación, actualizar estado, registrar histórico…).
* Tener un significado claro para el usuario: “inscripción correcta”, “ya inscrito”, etc.

### 2.3. Diseño REST “pragmático”

REST puro evita verbos en rutas, pero en APIs de negocio es habitual y aceptado usar acciones bien definidas, especialmente con DRF. Lo importante es:

* Ruta coherente y predecible.
* Método HTTP correcto (POST para acciones que cambian estado/crean algo, GET para consultas).
* Respuestas claras y códigos adecuados.

---

## 3. Decorador `@action` en DRF

### 3.1. Qué hace `@action`

`@action` permite añadir **rutas extra** dentro de un `ViewSet`.

Un `ModelViewSet` ya crea rutas CRUD automáticamente. Con `@action` le añades rutas como:

* `POST /cursos/{id}/inscribirse/`
* `GET /cursos/destacados/`

### 3.2. Dónde se usa

Se usa dentro de un `ViewSet`:

```python
from rest_framework.decorators import action
from rest_framework.response import Response
```

### 3.3. Parámetros principales 

#### `detail=True` o `detail=False`

* `detail=True`: la acción es **sobre un objeto concreto** (requiere `{id}`).

  * Ruta típica: `/cursos/5/inscribirse/`
  * Dentro del método puedes usar `self.get_object()` para obtener el objeto.
* `detail=False`: la acción es **sobre la colección** (no hay `{id}`).

  * Ruta típica: `/cursos/destacados/`
  * Sueles filtrar y devolver listas o estadísticas.

#### `methods=[...]`

Lista de métodos permitidos (en minúsculas):

* `methods=['post']` para acciones que cambian estado o crean relaciones.
* `methods=['get']` para consultas especiales.
* Evita usar `GET` para acciones que **modifican** datos.

#### `url_path='...'`

* Por defecto, el nombre de la función es la ruta.

  * `def inscribirse(...)` → `/inscribirse/`
* Con `url_path` puedes forzar otro:

  * `url_path='signup'` → `/signup/`

#### `permission_classes=[...]`

Permite cambiar permisos solo en esa acción (muy útil):

* Que el CRUD sea público pero `inscribirse` requiera estar autenticado.
* O que solo admins puedan ejecutar una acción.

Ejemplo:

```python
@action(detail=True, methods=['post'], permission_classes=[IsAuthenticated])
def inscribirse(self, request, pk=None):
    ...
```

> Nota: también existe `authentication_classes=[...]`, igual de útil si quieres un método de autenticación distinto para una acción concreta.

#### `serializer_class=...` (muy importante)

En acciones de negocio, a menudo necesitas un serializer de entrada distinto. Puedes:

* Definir un serializer específico en la acción.
* O hacer que `get_serializer_class()` devuelva uno u otro según `self.action`.

---

## 4. Preparación del proyecto para acciones de negocio

**No se instala nada extra** para que `@action` funcione.

---

## 5. Ejemplo completo: Acción de negocio `inscribirse`

### 5.1. Modelos mínimos implicados (contexto)

Para que el ejemplo tenga sentido, suele haber:

* `Curso`
* `Inscripcion` (relación entre Curso y Estudiante/Usuario o un modelo Estudiante)

**La lógica**: si un estudiante intenta inscribirse en un curso:

* Si falta `estudiante_id` → 400
* Si ya estaba inscrito → 409
* Si se crea correctamente → 201

### 5.2. Versión “directa” (como tu ejemplo)

Dentro del `CursoViewSet`:

```python
from rest_framework.decorators import action
from rest_framework.response import Response
from rest_framework import status

from .models import Curso, Inscripcion
from .serializers import CursoSerializer

class CursoViewSet(ModelViewSet):
    queryset = Curso.objects.all()
    serializer_class = CursoSerializer

    @action(detail=True, methods=['post'])
    def inscribirse(self, request, pk=None):
        curso = self.get_object()
        estudiante_id = request.data.get('estudiante_id')

        if not estudiante_id:
            return Response(
                {"error": "estudiante_id obligatorio"},
                status=status.HTTP_400_BAD_REQUEST
            )

        inscripcion, creada = Inscripcion.objects.get_or_create(
            curso=curso,
            estudiante_id=estudiante_id
        )

        if not creada:
            return Response(
                {"error": "ya inscrito"},
                status=status.HTTP_409_CONFLICT
            )

        return Response(
            {"mensaje": "inscripción correcta"},
            status=status.HTTP_201_CREATED
        )
```

### 5.3. Qué está pasando realmente (explicación teórica)

* `@action(detail=True, methods=['post'])`:

  * DRF añade una ruta extra colgada del curso.
* `curso = self.get_object()`:

  * DRF usa el `pk` de la URL y el `queryset` para obtener el `Curso`.
  * Si no existe, DRF responde automáticamente con `404 Not Found`.
* `request.data`:

  * En DRF, contiene el cuerpo parseado (JSON/form-data).
* `get_or_create(...)`:

  * Busca si existe una inscripción con esos campos.
  * Si no existe, la crea.
  * Devuelve `(objeto, True/False)`.

### 5.4. Por qué 409 en “ya inscrito”

`409 Conflict` es un código muy usado cuando el estado del recurso **entra en conflicto** con la operación:

* La operación “inscribirse” no tiene sentido si ya estabas inscrito.

---

## 6. Uso de serializadores de entrada (recomendado)

La versión anterior funciona, pero cuando la entrada se complica, el código se ensucia.

La solución limpia es usar un `Serializer` simple para validar.

### 6.1. Crear el serializer de entrada

En `tuapp/serializers.py`:

```python
from rest_framework import serializers

class InscribirseSerializer(serializers.Serializer):
    estudiante_id = serializers.IntegerField()
```

**Qué consigues con esto:**

* Validación automática de tipo (int).
* Mensajes de error estándar.
* Campo obligatorio por defecto.
* Código más mantenible.

### 6.2. Usarlo dentro de la acción

En `views.py`:

```python
from rest_framework import status
from rest_framework.decorators import action
from rest_framework.response import Response

from .models import Inscripcion
from .serializers import InscribirseSerializer

class CursoViewSet(ModelViewSet):
    ...

    @action(detail=True, methods=['post'])
    def inscribirse(self, request, pk=None):
        curso = self.get_object()

        serializer = InscribirseSerializer(data=request.data)
        serializer.is_valid(raise_exception=True)

        estudiante_id = serializer.validated_data['estudiante_id']

        inscripcion, creada = Inscripcion.objects.get_or_create(
            curso=curso,
            estudiante_id=estudiante_id
        )

        if not creada:
            return Response(
                {"error": "ya inscrito"},
                status=status.HTTP_409_CONFLICT
            )

        return Response(
            {"mensaje": "inscripción correcta"},
            status=status.HTTP_201_CREATED
        )
```

### 6.3. Qué hace `is_valid(raise_exception=True)`

* Si es inválido, DRF lanza una excepción controlada.
* DRF responde automáticamente con:

  * `400 Bad Request`
  * Un JSON con los errores por campo (formato estándar).
* Evitas escribir `if not estudiante_id: ...` para cada campo.

---

## 7. Acciones de colección (detail=False)

Son útiles para:

* Listados especiales (destacados, recientes, más valorados…)
* Estadísticas agregadas
* Búsquedas avanzadas si no quieres meterlo todo en filtros

Ejemplo:

```python
from rest_framework.decorators import action
from rest_framework.response import Response

class CursoViewSet(ModelViewSet):
    queryset = Curso.objects.all()
    serializer_class = CursoSerializer

    @action(detail=False, methods=['get'])
    def destacados(self, request):
        cursos = Curso.objects.filter(activo=True)
        serializer = self.get_serializer(cursos, many=True)
        return Response(serializer.data)
```

### 7.1. Por qué `self.get_serializer(...)`

* Respeta el serializer configurado para el viewset.
* Mantiene consistencia con el resto del API.
* Te permite cambiar el serializer globalmente sin tocar esta acción.

---

## 8. Rutas resultantes y cómo probar

Suponiendo que tienes el router registrando el viewset así:

```python
router.register(r'cursos', CursoViewSet)
```

Entonces:

### 8.1. Acción por objeto

* **POST** `/cursos/5/inscribirse/`
  Body JSON:

```json
{ "estudiante_id": 12 }
```

Respuestas típicas:

* `201` → inscripción correcta
* `400` → campo inválido o faltante
* `404` → curso no existe
* `409` → ya inscrito

### 8.2. Acción de colección

* **GET** `/cursos/destacados/`
  Responde lista de cursos filtrados.

---

## 9. Práctica del bloque (lo que debe hacer cada alumno)

Cada alumno, en su **temática de proyecto**, debe implementar:

### 9.1. Requisitos mínimos

1. **Al menos 1 acción de negocio** (detail=True o detail=False, pero se recomienda detail=True).
2. Usar `@action` correctamente:

   * Elegir bien `detail=...`
   * Elegir el método HTTP correcto
3. Control de errores:

   * 400 para validación
   * 404 si el objeto no existe (DRF lo hace con `get_object`)
   * 409 u otro código coherente si la operación no tiene sentido por estado
4. Si recibe datos “con chicha” (más de un campo o validaciones), usar **serializer de entrada**.



---

## 10. Ejemplos de acciones por temática 


* **Cursos**: `inscribirse`, `cancelar_inscripcion`, `finalizar`, `publicar`
* **Tienda**: `añadir_al_carrito`, `vaciar_carrito`, `finalizar_compra`
* **Eventos**: `confirmar_asistencia`, `cancelar_asistencia`
* **Reservas**: `confirmar`, `anular`, `checkin`
* **Tareas**: `marcar_completada`, `reabrir`, `asignar_usuario`
