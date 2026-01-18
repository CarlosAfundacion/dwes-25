# SEMANA 3

#  BLOQUE 4 – Relaciones entre modelos en la API REST (DRF)

## 1. Por qué este bloque es crítico en una API real

Hasta ahora, con CRUD, ya podemos crear, listar, modificar y borrar “cosas”.
Pero una API profesional no sirve si los recursos están “sueltos”.

En un sistema real, las entidades se conectan:

* Un **Libro** pertenece a una **Categoría** (1:N)
* Un **Pedido** tiene **Productos** (N:M con cantidad)
* Un **Usuario** tiene un **Perfil** (1:1)
* Un **Jugador** participa en un **Partido** (N:M con goles/minutos)

**Objetivo del bloque:** que el alumnado aprenda a **representar** esas relaciones en JSON y a **tomar decisiones correctas** de diseño API.

---

## 2. Tipos de relaciones y cómo “se ven” en JSON

### 2.1. 1:N (`ForeignKey`)

Un recurso tiene **un** padre, y el padre tiene **muchos** hijos.

* Curso → Categoría
* Libro → Autor
* Producto → Marca

En JSON suele representarse como:

* **ID del padre** (común)
* **Objeto anidado** (útil en detalles)
* **URL / hyperlink** (si se usa HyperlinkedModelSerializer)

---

### 2.2. N:M (`ManyToManyField`)

Un recurso se asocia con muchos y viceversa.

* Curso ↔ Etiquetas
* Película ↔ Actores

En JSON suele representarse como:

* Lista de IDs: `[2,4,7]`
* Lista de objetos anidados: `[{...},{...}]`

---

### 2.3. N:M con datos extra (`through`)

Es el caso más empresarial.

* Pedido ↔ Producto (cantidad, precio en el momento)
* Alumno ↔ Curso (fecha, nota)
* Jugador ↔ Partido (minutos, goles)

Aquí **NO** vale un ManyToMany simple, porque necesitamos **campos extra**.

---

### 2.4. 1:1 (`OneToOneField`)

Extiende un modelo con información adicional.

* Usuario ↔ Perfil
* Usuario ↔ Dirección principal

---

## 3. Conceptos fundamentales en DRF para relaciones

### 3.1. “Lectura” vs “Escritura” en serialización

En APIs reales, muchas veces se decide:

* GET: se devuelven objetos más ricos
* POST/PUT/PATCH: se envían IDs

Esto evita:

* complejidad
* serializadores gigantes
* validaciones difíciles

---

## 4. ForeignKey (1:N) en DRF: opciones habituales

### Modelo ejemplo

```python
class Curso(models.Model):
    titulo = models.CharField(max_length=200)
    categoria = models.ForeignKey(Categoria, on_delete=models.SET_NULL, null=True)
```

---

### Opción A (más común): representar por ID

```python
class CursoSerializer(serializers.ModelSerializer):
    class Meta:
        model = Curso
        fields = ['id', 'titulo', 'categoria']
```

**JSON típico:**

```json
{ "id": 1, "titulo": "Python", "categoria": 3 }
```

 Ligero
 
 Fácil de mantener
 
 Perfecto para listados

---

### Opción B: objeto anidado (solo lectura)

Para mostrar más info “de golpe” al frontend.

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

 Muy cómodo en GET
 
 No sirve directamente para POST con categoría anidada (sería más complejo)

---

### Opción C: “dos campos”: uno para leer, otro para escribir (muy profesional)

Este patrón es muy útil.

```python
class CursoSerializer(serializers.ModelSerializer):
    categoria_detalle = CategoriaSerializer(source="categoria", read_only=True)
    categoria = serializers.PrimaryKeyRelatedField(
        queryset=Categoria.objects.all(),
        required=False,
        allow_null=True
    )

    class Meta:
        model = Curso
        fields = ['id', 'titulo', 'categoria', 'categoria_detalle']
```

* `categoria` → sirve para escribir (ID)
* `categoria_detalle` → sirve para leer (objeto)

 Muy claro
 
 Muy usado en APIs reales

---

## 5. ManyToMany simple (N:M) en DRF: opciones y parámetros

### Modelo ejemplo

```python
class Curso(models.Model):
    etiquetas = models.ManyToManyField(Etiqueta, blank=True)
```

---

### Opción A: lista de IDs (estándar)

```python
class CursoSerializer(serializers.ModelSerializer):
    class Meta:
        model = Curso
        fields = ['id', 'titulo', 'etiquetas']
```

**JSON:**

```json
{ "id": 1, "titulo": "Python", "etiquetas": [2,4,7] }
```

---

### Opción B: anidado (solo lectura)

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

### Parámetros importantes en ManyToMany

* `blank=True`: permite que no haya etiquetas
* `related_name="cursos"`: para acceso inverso desde la etiqueta
* `through="ModeloIntermedio"`: si hay datos extra

---

## 6. Through model (N:M con atributos extra): cómo exponerlo bien

### Modelo intermedio

```python
class Inscripcion(models.Model):
    estudiante = models.ForeignKey(Estudiante, on_delete=models.CASCADE)
    curso = models.ForeignKey(Curso, on_delete=models.CASCADE)
    fecha_inscripcion = models.DateField(auto_now_add=True)
    nota_final = models.IntegerField(null=True, blank=True)
```

---

### Serializer del modelo intermedio

```python
class InscripcionSerializer(serializers.ModelSerializer):
    class Meta:
        model = Inscripcion
        fields = ['id', 'estudiante', 'curso', 'fecha_inscripcion', 'nota_final']
```

 Aquí ya estás exponiendo el “evento” real (la inscripción)

---

### Exponerlo desde un recurso principal

```python
class CursoSerializer(serializers.ModelSerializer):
    inscripciones = InscripcionSerializer(
        source="inscripcion_set",
        many=True,
        read_only=True
    )

    class Meta:
        model = Curso
        fields = ['id', 'titulo', 'inscripciones']
```

**Concepto clave:** `source="inscripcion_set"`

* Django crea automáticamente el acceso inverso: `curso.inscripcion_set`
* Si usas `related_name="inscripciones"` en el ForeignKey, sería más limpio:

```python
curso = models.ForeignKey(Curso, on_delete=models.CASCADE, related_name="inscripciones")
```

Y entonces en el serializer:

```python
inscripciones = InscripcionSerializer(many=True, read_only=True)
```

 Mucho más profesional

---

## 7. OneToOne (1:1) en DRF: patrones típicos

### Modelo

```python
class Perfil(models.Model):
    usuario = models.OneToOneField(User, on_delete=models.CASCADE)
    avatar = models.CharField(max_length=255, blank=True)
```

---

### Serializer anidado (lectura)

```python
class PerfilSerializer(serializers.ModelSerializer):
    class Meta:
        model = Perfil
        fields = ['avatar']

class UsuarioSerializer(serializers.ModelSerializer):
    perfil = PerfilSerializer(read_only=True)

    class Meta:
        model = User
        fields = ['id', 'username', 'perfil']
```

Si quieres que sea editable, normalmente se hacen endpoints específicos o lógica más avanzada (bloque 6).

---

## 8. Errores típicos del alumnado (y cómo evitarlos)

*  Anidar todo en listados → respuestas enormes
*  No controlar `read_only` / `queryset` → errores al crear
*  No usar `related_name` → código confuso con `xxx_set`
*  Intentar crear objetos relacionados “anidados” sin haberlo diseñado

---

##  PRÁCTICA OBLIGATORIA DEL ALUMNADO (BLOQUE 4)

Cada alumno debe adaptarlo a su temática y entregar:

1. Implementar y exponer en la API:

* Una relación **1:N**
* Una relación **N:M**
* Una relación **N:M con modelo intermedio** (si no existe, crear una)
* Una relación **1:1** (si no existe, crear un perfil o entidad equivalente)

2. En su `serializer` principal, aplicar al menos:

* Un patrón de lectura/escritura correcto (ej: `categoria` + `categoria_detalle`)
* Un `related_name` en al menos una relación para evitar `xxx_set`

---

# BLOQUE 5 – Filtros, búsqueda, ordenación y paginación

## 1. Por qué esto es “mínimo profesional”

Una API con `GET /recurso/` que devuelve todo “a lo bruto” se vuelve inútil cuando hay volumen.

En empresa se exige:

* filtrar por estado, categoría, fechas…
* buscar por texto
* ordenar
* paginar

---

## 2. Query params: concepto esencial

Los **query parameters** son datos que van en la URL y modifican la consulta:

* filtrar: `?activo=true`
* buscar: `?search=python`
* ordenar: `?ordering=-precio`
* paginar: `?page=2`

La ruta no cambia, cambia el comportamiento.

---

## 3. django-filter (filtros por campos) en DRF

### Instalación

```bash
pip install django-filter
```

### Activación en `settings.py`

```python
INSTALLED_APPS = [
    ...
    'django_filters',
]
```

---

## 4. filter_backends: qué es y para qué sirve

En DRF, `filter_backends` define “motores” que pueden alterar el queryset.

Ejemplo típico:

```python
filter_backends = [
    DjangoFilterBackend,
    SearchFilter,
    OrderingFilter
]
```

* `DjangoFilterBackend` → filtra por campos exactos
* `SearchFilter` → búsqueda textual
* `OrderingFilter` → ordenación

---

## 5. Filtros simples con `filterset_fields`

```python
from django_filters.rest_framework import DjangoFilterBackend

class CursoViewSet(ModelViewSet):
    queryset = Curso.objects.all()
    serializer_class = CursoSerializer
    filter_backends = [DjangoFilterBackend]
    filterset_fields = ['activo', 'nivel', 'categoria']
```

### Qué permite esto

* filtrar por booleanos
* filtrar por choices
* filtrar por ForeignKey (por ID)

---

## 6. Filtros avanzados con FilterSet (opcional pero muy recomendable)

Cuando quieres filtrar por:

* rangos
* fechas
* búsquedas parciales
* comparaciones

```python
import django_filters

class CursoFilter(django_filters.FilterSet):
    precio_min = django_filters.NumberFilter(field_name="precio", lookup_expr="gte")
    precio_max = django_filters.NumberFilter(field_name="precio", lookup_expr="lte")

    class Meta:
        model = Curso
        fields = ['activo', 'nivel', 'categoria']
```

Y en el ViewSet:

```python
class CursoViewSet(ModelViewSet):
    queryset = Curso.objects.all()
    serializer_class = CursoSerializer
    filter_backends = [DjangoFilterBackend]
    filterset_class = CursoFilter
```

**Conceptos nuevos:**

* `lookup_expr="gte"` → >=
* `lookup_expr="lte"` → <=

---

## 7. Búsqueda textual: `SearchFilter`

```python
from rest_framework.filters import SearchFilter

class CursoViewSet(ModelViewSet):
    ...
    filter_backends = [DjangoFilterBackend, SearchFilter]
    search_fields = ['titulo', 'descripcion']
```

### Opciones habituales en `search_fields`

* `['titulo']` → busca por ese campo
* `['^titulo']` → “empieza por”
* `['=titulo']` → exacto


---

## 8. Ordenación: `OrderingFilter`

```python
from rest_framework.filters import OrderingFilter

class CursoViewSet(ModelViewSet):
    ...
    filter_backends = [DjangoFilterBackend, SearchFilter, OrderingFilter]
    ordering_fields = ['precio', 'fecha_inicio', 'nivel']
    ordering = ['fecha_inicio']
```

### Conceptos

* `ordering_fields` limita por qué campos se puede ordenar (seguridad y control)
* `ordering` define orden por defecto

---

## 9. Paginación: por qué y cómo

La paginación evita:

* respuestas gigantes
* tiempos altos
* consumo de red innecesario

### Configuración global (recomendada)

```python
REST_FRAMEWORK = {
    'DEFAULT_PAGINATION_CLASS': 'rest_framework.pagination.PageNumberPagination',
    'PAGE_SIZE': 5
}
```

### Alternativas típicas

* `LimitOffsetPagination` (muy común en APIs)
* `CursorPagination` (más avanzada, eficiente para grandes volúmenes)

---

##  PRÁCTICA OBLIGATORIA DEL ALUMNADO (BLOQUE 5)

Cada alumno, en uno de sus recursos principales, debe:

1. Activar:

* filtros básicos (`filterset_fields`)
* búsqueda (`search_fields`)
* ordenación (`ordering_fields`)
* paginación global (en settings)

2. Añadir **al menos un filtro avanzado**:

* rango numérico (min/max) o rango de fechas

---


#  BLOQUE 6 – Endpoints de negocio y acciones personalizadas (`@action`)

## 1. Idea clave: una API real no es solo CRUD

CRUD cubre operaciones básicas sobre una tabla.

Pero las aplicaciones reales tienen procesos:

* Inscribir un alumno en un curso
* Confirmar un pedido
* Marcar un producto como favorito
* Publicar un contenido
* Finalizar un partido

Esto son **acciones** de negocio.

---

## 2. Qué es una acción de negocio en una API

Es un endpoint que representa un **verbo** y ejecuta lógica.

Ejemplo conceptual:

* `POST /cursos/{id}/inscribirse/`
* `POST /pedidos/{id}/confirmar/`
* `POST /partidos/{id}/finalizar/`

---

## 3. Decorador `@action`: qué hace y parámetros

En DRF, `@action` añade rutas extra a un ViewSet sin romper el router.

Parámetros importantes:

* `detail=True` → requiere ID (`/cursos/5/inscribirse/`)
* `detail=False` → no requiere ID (`/cursos/destacados/`)
* `methods=['post']` → método(s) permitidos
* `url_path='...'` → personaliza el nombre en la URL
* `permission_classes=[...]` → permisos específicos para esa acción

---

## 4. Ejemplo realista: inscribirse en un curso

### Acción básica

```python
from rest_framework.decorators import action
from rest_framework.response import Response
from rest_framework import status

class CursoViewSet(ModelViewSet):
    queryset = Curso.objects.all()
    serializer_class = CursoSerializer

    @action(detail=True, methods=['post'])
    def inscribirse(self, request, pk=None):
        curso = self.get_object()
        estudiante_id = request.data.get('estudiante_id')

        if not estudiante_id:
            return Response(
                {"error": "estudiante_id es obligatorio"},
                status=status.HTTP_400_BAD_REQUEST
            )

        inscripcion, creada = Inscripcion.objects.get_or_create(
            curso=curso,
            estudiante_id=estudiante_id
        )

        if not creada:
            return Response(
                {"error": "El estudiante ya está inscrito en este curso"},
                status=status.HTTP_409_CONFLICT
            )

        return Response(
            {"mensaje": "Inscripción realizada correctamente"},
            status=status.HTTP_201_CREATED
        )
```

### Conceptos explicados

* `self.get_object()` obtiene el recurso del `pk` y aplica permisos.
* `get_or_create()` evita duplicados sin reventar por restricción única.
* `409 CONFLICT` se usa cuando la acción no puede completarse por estado previo.

---

## 5. Validación: cómo hacerlo de forma limpia

En endpoints de negocio, valida siempre:

* existencia de datos requeridos
* coherencia de negocio
* permisos (en bloques posteriores)

Ejemplo:

```python
if nota < 0 or nota > 10:
    return Response({"error": "nota inválida"}, status=400)
```

---

## 6. Acciones con serializador específico (muy recomendable)

En lugar de leer `request.data` “a mano”, puedes definir un serializer de entrada.

```python
class InscribirseSerializer(serializers.Serializer):
    estudiante_id = serializers.IntegerField()
```

Y usarlo:

```python
@action(detail=True, methods=['post'])
def inscribirse(self, request, pk=None):
    serializer = InscribirseSerializer(data=request.data)
    serializer.is_valid(raise_exception=True)

    estudiante_id = serializer.validated_data['estudiante_id']
    ...
```

 Validación automática
 
 Errores consistentes
 
 Código más limpio

---

## 7. Acciones de colección (`detail=False`)

Ejemplo: devolver cursos destacados

```python
@action(detail=False, methods=['get'])
def destacados(self, request):
    cursos = Curso.objects.filter(activo=True).order_by('-created_at')[:10]
    serializer = self.get_serializer(cursos, many=True)
    return Response(serializer.data)
```

---

## 8. Buenas prácticas profesionales

* No usar GET para acciones que modifican datos
* Devolver códigos HTTP correctos
* Usar serializadores de entrada si hay lógica
* No meter lógica de negocio compleja en `Serializer.save()` si no hace falta
* Mantener nombres claros: `inscribirse`, `confirmar`, `finalizar`

---

##  PRÁCTICA OBLIGATORIA DEL ALUMNADO (BLOQUE 6)

Cada alumno debe implementar **al menos 1 endpoint de negocio** adaptado a su temática:

Ejemplos por temática:

* Biblioteca: `POST /prestamos/{id}/devolver/`
* Tienda: `POST /pedidos/{id}/confirmar/`
* Liga: `POST /partidos/{id}/finalizar/`
* Streaming: `POST /peliculas/{id}/valorar/`

Requisitos técnicos obligatorios:

1. Usar `@action` en un ViewSet
2. Elegir correctamente:

* `detail=True` si depende de un recurso concreto
* `detail=False` si es una acción global

3. Controlar errores:

* falta de datos → 400
* acción no válida por estado → 409 o 400 (según caso)

4. Usar **un serializer de entrada** si la acción recibe varios datos

---
