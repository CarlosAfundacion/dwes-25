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

---

## 1. Objetivo del bloque

El objetivo de este bloque es aprender a **hacer usable y escalable una API**, permitiendo:

* Filtrar resultados
* Buscar por texto
* Ordenar datos
* Paginar respuestas

Una API sin estos mecanismos **no es aceptable en un entorno profesional**.

---

## 2. Query parameters

Los query parameters modifican el comportamiento de una petición sin cambiar la ruta.

Ejemplos:

* `?activo=true`
* `?search=python`
* `?ordering=-precio`
* `?page=2`

---

## 3. Filtros por campos con `django-filter`

Configuración básica en el ViewSet:

```python
from django_filters.rest_framework import DjangoFilterBackend

class CursoViewSet(ModelViewSet):
    queryset = Curso.objects.all()
    serializer_class = CursoSerializer
    filter_backends = [DjangoFilterBackend]
    filterset_fields = ['activo', 'nivel', 'categoria']
```

Permite filtrar por:

* Booleanos
* Choices
* Claves foráneas (por ID)

---

## 4. Filtros avanzados con `FilterSet`

```python
import django_filters

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

---

## 5. Búsqueda textual

```python
from rest_framework.filters import SearchFilter

search_fields = ['titulo', 'descripcion']
```

Opciones habituales:

* Búsqueda parcial
* No distingue mayúsculas
* Adecuada para texto libre

---

## 6. Ordenación de resultados

```python
from rest_framework.filters import OrderingFilter

ordering_fields = ['precio', 'fecha_inicio']
ordering = ['fecha_inicio']
```

---

## 7. Paginación

Configuración global recomendada:

```python
REST_FRAMEWORK = {
    'DEFAULT_PAGINATION_CLASS': 
        'rest_framework.pagination.PageNumberPagination',
    'PAGE_SIZE': 5
}
```

Evita respuestas excesivas y mejora el rendimiento.

---

## 8. Práctica

Cada alumno debe:

1. Activar en un recurso principal:

   * Filtros
   * Búsqueda
   * Ordenación
   * Paginación

2. Implementar al menos un filtro avanzado (rango o fechas)

3. Entregar:

   * ViewSet actualizado
   * FilterSet 
   * Evidencias de funcionamiento

---

---

# BLOQUE 6 – Endpoints de negocio y acciones personalizadas

---

## 1. Objetivo del bloque

Aprender que una API real **no se limita al CRUD**, sino que debe representar **procesos de negocio**.

---

## 2. Qué es un endpoint de negocio

Es un endpoint que:

* Representa una acción
* Ejecuta lógica
* Puede afectar a varios modelos
* Tiene un significado funcional

Ejemplos:

* Inscribirse
* Confirmar
* Finalizar
* Valorar

---

## 3. Decorador `@action` en DRF

Permite añadir rutas personalizadas a un ViewSet.

Parámetros principales:

* `detail=True` o `False`
* `methods=['post']`
* `url_path`
* `permission_classes`

---

## 4. Ejemplo completo de acción de negocio

```python
@action(detail=True, methods=['post'])
def inscribirse(self, request, pk=None):
    curso = self.get_object()
    estudiante_id = request.data.get('estudiante_id')

    if not estudiante_id:
        return Response(
            {"error": "estudiante_id obligatorio"},
            status=400
        )

    inscripcion, creada = Inscripcion.objects.get_or_create(
        curso=curso,
        estudiante_id=estudiante_id
    )

    if not creada:
        return Response(
            {"error": "ya inscrito"},
            status=409
        )

    return Response(
        {"mensaje": "inscripción correcta"},
        status=201
    )
```

---

## 5. Uso de serializadores de entrada

```python
class InscribirseSerializer(serializers.Serializer):
    estudiante_id = serializers.IntegerField()
```

Ventajas:

* Validación automática
* Código más limpio
* Errores coherentes

---

## 6. Acciones de colección

```python
@action(detail=False, methods=['get'])
def destacados(self, request):
    cursos = Curso.objects.filter(activo=True)
    serializer = self.get_serializer(cursos, many=True)
    return Response(serializer.data)
```

---

## 7. Práctica 

Cada alumno debe:

1. Implementar al menos **una acción de negocio**
2. Usar `@action` correctamente
3. Controlar errores
4. Usar un serializer de entrada si recibe datos complejos

Entregar:

* ViewSet con la acción
* Serializer de entrada
* Evidencias según el anexo

---
