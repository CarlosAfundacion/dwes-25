# Guía completa para documentar una API REST Django con drf-spectacular y Swagger UI

## 1. Introducción

Cuando desarrollamos una API REST, no basta con que los endpoints funcionen correctamente. Una API profesional debe ser comprensible, fácil de probar y fácil de consumir por otras aplicaciones. Para ello necesitamos una documentación clara que explique:

* Qué endpoints existen.
* Qué métodos HTTP acepta cada endpoint.
* Qué datos hay que enviar en cada petición.
* Qué datos devuelve cada respuesta.
* Qué códigos de estado puede devolver la API.
* Qué endpoints requieren autenticación.
* Qué filtros, búsquedas, ordenaciones o parámetros admite cada recurso.
* Qué errores pueden aparecer y cómo interpretarlos.

En una API desarrollada con Django REST Framework, la documentación puede generarse automáticamente a partir del código. Esto evita tener que mantener una documentación manual separada, que suele quedarse desactualizada. Una de las herramientas más utilizadas para este propósito es **drf-spectacular**.

`drf-spectacular` genera un documento **OpenAPI 3** a partir de las vistas, serializers, rutas, permisos y configuración de Django REST Framework. A partir de ese documento OpenAPI podemos mostrar una interfaz interactiva con **Swagger UI**.

## 2. Qué es OpenAPI

**OpenAPI** es una especificación estándar para describir APIs REST. Antes era conocida como Swagger Specification. Un documento OpenAPI describe formalmente una API indicando:

* Información general de la API: título, descripción, versión, licencia, servidores, etc.
* Rutas disponibles.
* Métodos HTTP disponibles en cada ruta.
* Parámetros de consulta, ruta, cabecera o cookie.
* Cuerpos de petición.
* Respuestas posibles.
* Esquemas de datos.
* Sistemas de autenticación.
* Ejemplos de uso.

Un documento OpenAPI puede escribirse en formato **YAML** o **JSON**.

Ejemplo simplificado:

```yaml
openapi: 3.0.3
info:
  title: API de cursos
  version: 1.0.0
paths:
  /api/cursos/:
    get:
      summary: Listar cursos
      responses:
        '200':
          description: Lista de cursos
```

En un proyecto real no escribiremos todo esto a mano. `drf-spectacular` lo generará automáticamente a partir del código de Django REST Framework, y nosotros lo iremos mejorando cuando sea necesario.

## 3. Qué es Swagger UI

**Swagger UI** es una interfaz web interactiva que permite visualizar y probar una API REST desde el navegador. Permite:

* Ver todos los endpoints agrupados por etiquetas.
* Consultar qué datos recibe cada endpoint.
* Consultar qué datos devuelve cada endpoint.
* Probar peticiones reales desde el navegador.
* Enviar tokens de autenticación.
* Ver respuestas reales de la API.

Por ejemplo, si nuestra API tiene un endpoint:

```http
GET /api/cursos/
```

Swagger UI mostrará ese endpoint, sus parámetros de consulta, su posible respuesta y un botón para ejecutarlo.

En esta guía usaremos únicamente:

* `drf-spectacular` para generar el schema OpenAPI.
* `Swagger UI` para visualizar y probar la API.

No usaremos ReDoc.

## 4. Por qué usar drf-spectacular

`drf-spectacular` es recomendable porque:

* Genera documentación OpenAPI 3 automáticamente.
* Se integra muy bien con Django REST Framework.
* Detecta serializers, ViewSets, routers, filtros, paginación y autenticación.
* Permite documentar endpoints personalizados creados con `@action`.
* Permite añadir ejemplos reales de petición y respuesta.
* Permite mejorar descripciones, parámetros y códigos de estado.
* Permite exportar la documentación en YAML o JSON.
* Permite usar Swagger UI para probar la API.

La idea fundamental es esta:

> Primero dejamos que `drf-spectacular` genere la documentación automáticamente. Después corregimos o enriquecemos lo que no pueda deducir correctamente.

## 5. Punto de partida del proyecto de ejemplo

El proyecto usado como referencia es una API de cursos con Django REST Framework.

Tiene modelos como:

* `Categoria`
* `Etiqueta`
* `Instructor`
* `Estudiante`
* `Curso`
* `Inscripcion`

También utiliza:

* ViewSets.
* Routers.
* Serializers.
* Filtros con `django-filter`.
* Búsqueda con `SearchFilter`.
* Ordenación con `OrderingFilter`.
* Paginación con `PageNumberPagination`.
* Autenticación JWT con `djangorestframework-simplejwt`.
* Acciones personalizadas como `destacados` e `inscribirse`.

Este tipo de proyecto es muy adecuado para aprender documentación de APIs, porque tiene una estructura parecida a la que podemos encontrar en una API real.

## 6. Instalación de drf-spectacular

Desde el entorno virtual del proyecto, instalamos el paquete:

```bash
pip install drf-spectacular
```

Si el proyecto tiene un archivo `requirements.txt`, conviene actualizarlo:

```bash
pip freeze > requirements.txt
```

O añadir manualmente una línea similar a:

```txt
drf-spectacular
```

## 7. Añadir drf-spectacular a INSTALLED_APPS

En `settings.py`, buscamos la lista `INSTALLED_APPS` y añadimos `drf_spectacular`.

Ejemplo:

```python
INSTALLED_APPS = [
    'cursos',
    'rest_framework',
    'drf_spectacular',
    'corsheaders',
    'django_filters',
    'django.contrib.admin',
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.messages',
    'django.contrib.staticfiles',
]
```

Es importante escribir:

```python
'drf_spectacular'
```

No hay que escribir `drf-spectacular` con guion, porque ese es el nombre del paquete de instalación, no el nombre de la app Django.

## 8. Configurar Django REST Framework para usar spectacular

En el mismo archivo `settings.py`, localizamos la variable `REST_FRAMEWORK`.

El proyecto ya puede tener configuración previa, por ejemplo:

```python
REST_FRAMEWORK = {
    "DEFAULT_PAGINATION_CLASS": "rest_framework.pagination.PageNumberPagination",
    "PAGE_SIZE": 5,
    'DEFAULT_AUTHENTICATION_CLASSES': (
        'rest_framework_simplejwt.authentication.JWTAuthentication',
    ),
}
```

No debemos sustituir esta configuración. Debemos ampliarla añadiendo:

```python
'DEFAULT_SCHEMA_CLASS': 'drf_spectacular.openapi.AutoSchema',
```

La configuración completa quedaría así:

```python
REST_FRAMEWORK = {
    "DEFAULT_PAGINATION_CLASS": "rest_framework.pagination.PageNumberPagination",
    "PAGE_SIZE": 5,
    'DEFAULT_AUTHENTICATION_CLASSES': (
        'rest_framework_simplejwt.authentication.JWTAuthentication',
    ),
    'DEFAULT_SCHEMA_CLASS': 'drf_spectacular.openapi.AutoSchema',
}
```

Esta línea indica a Django REST Framework que debe usar `drf-spectacular` como generador de schema OpenAPI.

## 9. Añadir configuración general de SPECTACULAR_SETTINGS

Debajo de `REST_FRAMEWORK`, añadimos una variable llamada `SPECTACULAR_SETTINGS`.

Ejemplo recomendado para este proyecto:

```python
SPECTACULAR_SETTINGS = {
    'TITLE': 'API de cursos',
    'DESCRIPTION': 'API REST para gestionar cursos, categorías, etiquetas, instructores, estudiantes e inscripciones.',
    'VERSION': '1.0.0',
    'SERVE_INCLUDE_SCHEMA': False,
}
```

Significado de cada opción:

* `TITLE`: nombre que aparecerá en la documentación.
* `DESCRIPTION`: descripción general de la API.
* `VERSION`: versión de la API.
* `SERVE_INCLUDE_SCHEMA`: si está en `False`, evita que el endpoint del schema aparezca listado dentro de la propia documentación como un endpoint funcional más.

Una versión más completa podría ser:

```python
SPECTACULAR_SETTINGS = {
    'TITLE': 'API de cursos',
    'DESCRIPTION': '''
API REST de ejemplo para trabajar con cursos online.

Permite gestionar categorías, etiquetas, instructores, cursos, estudiantes e inscripciones.
Incluye autenticación mediante JWT, filtrado, búsqueda, ordenación y acciones personalizadas.
''',
    'VERSION': '1.0.0',
    'SERVE_INCLUDE_SCHEMA': False,
    'CONTACT': {
        'name': 'Departamento de Informática',
        'email': 'informatica@example.com',
    },
    'LICENSE': {
        'name': 'Uso educativo',
    },
}
```

## 10. Configurar las URLs de documentación

En el archivo principal `urls.py` del proyecto, importamos las vistas de spectacular:

```python
from drf_spectacular.views import SpectacularAPIView, SpectacularSwaggerView
```

Después añadimos nuevas rutas a `urlpatterns`.

Ejemplo completo:

```python
from django.contrib import admin
from django.urls import path, include
from cursos.routers import router as cursos_router
from rest_framework_simplejwt.views import (
    TokenObtainPairView,
    TokenRefreshView,
)
from drf_spectacular.views import (
    SpectacularAPIView,
    SpectacularSwaggerView,
)

urlpatterns = [
    path('admin/', admin.site.urls),

    # Autenticación JWT
    path('api/token/', TokenObtainPairView.as_view(), name='token_obtain_pair'),
    path('api/token/refresh/', TokenRefreshView.as_view(), name='token_refresh'),

    # Endpoints de la API
    path('api/', include(cursos_router.urls)),

    # Schema OpenAPI
    path('api/schema/', SpectacularAPIView.as_view(), name='schema'),

    # Documentación Swagger UI
    path('api/docs/', SpectacularSwaggerView.as_view(url_name='schema'), name='swagger-ui'),
]
```

A partir de este momento tendremos dos URLs nuevas:

```txt
/api/schema/
/api/docs/
```

Uso recomendado:

* `/api/schema/`: documento OpenAPI en formato JSON o YAML.
* `/api/docs/`: documentación interactiva con Swagger UI.

## 11. Probar la documentación en el navegador

Arrancamos el servidor:

```bash
python manage.py runserver
```

Abrimos en el navegador:

```txt
http://127.0.0.1:8000/api/docs/
```

Deberíamos ver la documentación generada automáticamente con Swagger UI.

También podemos comprobar el schema:

```txt
http://127.0.0.1:8000/api/schema/
```

## 12. Exportar la documentación OpenAPI a un archivo

Además de consultar la documentación desde el navegador, podemos exportar el schema a un archivo.

Para generar un archivo YAML:

```bash
python manage.py spectacular --file schema.yml
```

Para generar un archivo JSON:

```bash
python manage.py spectacular --file schema.json
```

Esto es útil para:

* Entregar la documentación del proyecto.
* Subirla al repositorio.
* Validarla en herramientas externas.
* Generar clientes automáticos para frontend.
* Compartir la API con otros equipos.

## 13. Validar el schema generado

Podemos pedir a spectacular que valide el schema:

```bash
python manage.py spectacular --file schema.yml --validate
```

Si aparecen advertencias, no siempre significan que la API esté mal. Muchas veces indican que spectacular no ha podido deducir alguna información automáticamente.

Ejemplos habituales:

* No puede saber exactamente qué serializer devuelve una acción personalizada.
* No puede deducir el tipo exacto de un `SerializerMethodField`.
* No sabe qué códigos de error puede devolver una vista.
* No conoce ejemplos reales de petición o respuesta.

La solución será mejorar la documentación usando decoradores.

## 14. Qué documentación genera automáticamente

`drf-spectacular` puede detectar muchas cosas por sí solo:

### 14.1. Endpoints creados por ViewSets y Routers

Si usamos un `DefaultRouter`, spectacular detecta rutas como:

```txt
/api/cursos/
/api/cursos/{id}/
/api/categorias/
/api/etiquetas/
/api/instructores/
/api/estudiantes/
/api/inscripciones/
```

También detecta las acciones estándar de un `ModelViewSet`:

* `list`: GET sobre la colección.
* `retrieve`: GET sobre un objeto concreto.
* `create`: POST.
* `update`: PUT.
* `partial_update`: PATCH.
* `destroy`: DELETE.

### 14.2. Serializers

A partir de los serializers, spectacular detecta:

* Campos obligatorios.
* Campos opcionales.
* Campos de solo lectura.
* Tipos de dato.
* Relaciones por ID.
* Campos anidados.
* Listas.
* Fechas.
* Decimales.
* Booleanos.

Por ejemplo, un serializer de curso con campos como `titulo`, `descripcion`, `precio`, `fecha_inicio`, `nivel` y `activo` generará un esquema de datos para el recurso `Curso`.

### 14.3. Paginación

Si la API usa `PageNumberPagination`, spectacular documentará respuestas paginadas con una estructura similar a:

```json
{
  "count": 25,
  "next": "http://127.0.0.1:8000/api/cursos/?page=2",
  "previous": null,
  "results": [
    {
      "id": 1,
      "titulo": "Django REST Framework",
      "descripcion": "Curso introductorio",
      "precio": "49.99"
    }
  ]
}
```

### 14.4. Filtros, búsqueda y ordenación

Si usamos:

```python
filter_backends = [DjangoFilterBackend, SearchFilter, OrderingFilter]
```

spectacular puede detectar parámetros como:

```txt
?search=django
?ordering=precio
?ordering=-fecha_inicio
?activo=true
?nivel=1
?categoria=2
```

En el proyecto de ejemplo también existen filtros personalizados para precio mínimo y precio máximo:

```txt
?precio_min=10
?precio_max=100
```

En algunos casos conviene documentarlos manualmente para que el alumnado vea descripciones más claras.

### 14.5. Autenticación JWT

Como el proyecto usa SimpleJWT, la documentación puede mostrar el sistema de autenticación mediante token Bearer.

El flujo habitual será:

1. El usuario envía usuario y contraseña a `/api/token/`.
2. La API devuelve un `access` token y un `refresh` token.
3. El cliente usa el token de acceso en cada petición protegida.
4. El token se envía en la cabecera HTTP:

```http
Authorization: Bearer <token>
```

## 15. Por qué hay que mejorar la documentación automática

La documentación automática es muy útil, pero no siempre es suficiente. El generador puede detectar la estructura técnica, pero no siempre conoce la intención educativa o funcional de la API.

Por ejemplo, puede detectar que existe este endpoint:

```http
POST /api/cursos/{id}/inscribirse/
```

Pero no necesariamente documentará con suficiente claridad:

* Que requiere autenticación.
* Que inscribe al estudiante asociado al usuario autenticado.
* Que puede devolver `201 Created` si la inscripción se crea correctamente.
* Que puede devolver `400 Bad Request` si el usuario no tiene perfil de estudiante.
* Que puede devolver `409 Conflict` si el estudiante ya estaba inscrito.
* Que no necesita cuerpo de petición.

Ahí es donde usamos las herramientas de personalización de spectacular.

## 16. Importar utilidades de drf-spectacular

En `views.py`, añadimos los imports necesarios:

```python
from drf_spectacular.utils import (
    extend_schema,
    extend_schema_view,
    OpenApiParameter,
    OpenApiExample,
    OpenApiResponse,
    OpenApiTypes,
    inline_serializer,
)
from rest_framework import serializers
```

Estos elementos sirven para:

* `extend_schema`: documentar un método concreto o una acción.
* `extend_schema_view`: documentar varios métodos de un ViewSet.
* `OpenApiParameter`: documentar parámetros de consulta, ruta, cabecera, etc.
* `OpenApiExample`: añadir ejemplos de petición o respuesta.
* `OpenApiResponse`: describir respuestas concretas.
* `OpenApiTypes`: usar tipos básicos como string, int, bool, date, etc.
* `inline_serializer`: definir serializers pequeños directamente en la documentación cuando no merece la pena crear una clase aparte.

## 17. Mejorar la documentación de un ViewSet completo

Podemos documentar las operaciones estándar de un ViewSet usando `@extend_schema_view`.

Ejemplo para `CategoriaViewSet`:

```python
@extend_schema_view(
    list=extend_schema(
        summary="Listar categorías",
        description="Devuelve el listado de categorías disponibles para clasificar los cursos.",
        tags=["Categorías"],
    ),
    retrieve=extend_schema(
        summary="Obtener una categoría",
        description="Devuelve los datos de una categoría concreta a partir de su identificador.",
        tags=["Categorías"],
    ),
    create=extend_schema(
        summary="Crear una categoría",
        description="Crea una nueva categoría. Requiere autenticación.",
        tags=["Categorías"],
    ),
    update=extend_schema(
        summary="Actualizar una categoría completa",
        description="Actualiza todos los campos de una categoría existente. Requiere autenticación.",
        tags=["Categorías"],
    ),
    partial_update=extend_schema(
        summary="Actualizar parcialmente una categoría",
        description="Actualiza solo algunos campos de una categoría existente. Requiere autenticación.",
        tags=["Categorías"],
    ),
    destroy=extend_schema(
        summary="Eliminar una categoría",
        description="Elimina una categoría existente. Requiere autenticación.",
        tags=["Categorías"],
    ),
)
class CategoriaViewSet(viewsets.ModelViewSet):
    queryset = Categoria.objects.all()
    serializer_class = CategoriaSerializer

    def get_permissions(self):
        if self.action in ["list", "retrieve"]:
            return [AllowAny()]
        return [IsAuthenticated()]
```

Ventajas:

* La documentación queda más clara.
* Los endpoints aparecen agrupados bajo la etiqueta `Categorías`.
* Cada operación tiene un resumen comprensible.
* Se diferencia la lectura pública de las operaciones protegidas.

## 18. Mejorar la documentación del ViewSet de cursos

El ViewSet de cursos es el más interesante porque incluye filtros, búsqueda, ordenación, serializers distintos y acciones personalizadas.

Ejemplo completo:

```python
@extend_schema_view(
    list=extend_schema(
        summary="Listar cursos",
        description="""
Devuelve una lista paginada de cursos.

Permite filtrar por estado, nivel, categoría y rango de precios. También permite realizar búsquedas por título o descripción y ordenar los resultados.
""",
        tags=["Cursos"],
        parameters=[
            OpenApiParameter(
                name="activo",
                type=OpenApiTypes.BOOL,
                location=OpenApiParameter.QUERY,
                description="Filtra los cursos activos o inactivos. Ejemplo: true",
                required=False,
            ),
            OpenApiParameter(
                name="nivel",
                type=OpenApiTypes.STR,
                location=OpenApiParameter.QUERY,
                description="Filtra por nivel del curso: 1 básico, 2 intermedio, 3 avanzado.",
                required=False,
                enum=["1", "2", "3"],
            ),
            OpenApiParameter(
                name="categoria",
                type=OpenApiTypes.INT,
                location=OpenApiParameter.QUERY,
                description="Filtra por el identificador de la categoría.",
                required=False,
            ),
            OpenApiParameter(
                name="precio_min",
                type=OpenApiTypes.NUMBER,
                location=OpenApiParameter.QUERY,
                description="Precio mínimo del curso.",
                required=False,
            ),
            OpenApiParameter(
                name="precio_max",
                type=OpenApiTypes.NUMBER,
                location=OpenApiParameter.QUERY,
                description="Precio máximo del curso.",
                required=False,
            ),
            OpenApiParameter(
                name="search",
                type=OpenApiTypes.STR,
                location=OpenApiParameter.QUERY,
                description="Busca texto en el título o la descripción del curso.",
                required=False,
            ),
            OpenApiParameter(
                name="ordering",
                type=OpenApiTypes.STR,
                location=OpenApiParameter.QUERY,
                description="Ordena por precio, fecha_inicio, titulo o created_at. Usar '-' delante para orden descendente. Ejemplo: -precio",
                required=False,
            ),
        ],
    ),
    retrieve=extend_schema(
        summary="Obtener detalle de un curso",
        description="Devuelve el detalle completo de un curso, incluyendo categoría, instructor, etiquetas e inscripciones.",
        tags=["Cursos"],
        responses={200: CursoDetalleSerializer},
    ),
    create=extend_schema(
        summary="Crear curso",
        description="Crea un nuevo curso. Requiere autenticación mediante token JWT.",
        tags=["Cursos"],
        request=CursoSerializer,
        responses={201: CursoSerializer},
    ),
    update=extend_schema(
        summary="Actualizar curso completo",
        description="Actualiza todos los campos de un curso existente. Requiere autenticación.",
        tags=["Cursos"],
    ),
    partial_update=extend_schema(
        summary="Actualizar parcialmente un curso",
        description="Actualiza solo algunos campos de un curso existente. Requiere autenticación.",
        tags=["Cursos"],
    ),
    destroy=extend_schema(
        summary="Eliminar curso",
        description="Elimina un curso existente. Requiere autenticación.",
        tags=["Cursos"],
    ),
)
class CursoViewSet(viewsets.ModelViewSet):
    queryset = (
        Curso.objects.all()
        .select_related("categoria", "instructor")
        .prefetch_related("etiquetas")
    )

    filter_backends = [DjangoFilterBackend, SearchFilter, OrderingFilter]
    filterset_class = CursoFilter
    search_fields = ["titulo", "descripcion"]
    ordering_fields = ["precio", "fecha_inicio", "titulo", "created_at"]
    ordering = ["fecha_inicio"]

    def get_permissions(self):
        if self.action in ["list", "retrieve"]:
            return [AllowAny()]
        return [IsAuthenticated()]

    def get_serializer_class(self):
        if self.action == "retrieve":
            return CursoDetalleSerializer
        return CursoSerializer
```

## 19. Documentar acciones personalizadas con @action

Las acciones personalizadas son endpoints que no forman parte del CRUD estándar.

En este proyecto hay dos acciones interesantes:

```python
@action(detail=False, methods=["get"], permission_classes=[IsAuthenticated])
def destacados(self, request):
    ...
```

Y:

```python
@action(detail=True, methods=["post"], permission_classes=[IsAuthenticated])
def inscribirse(self, request, pk=None):
    ...
```

Estas acciones deben documentarse manualmente para que queden claras.

## 20. Documentar la acción destacados

La acción `destacados` devuelve cursos activos. Como está definida con `detail=False`, no trabaja sobre un curso concreto, sino sobre la colección.

Ruta generada por el router:

```http
GET /api/cursos/destacados/
```

Ejemplo de documentación:

```python
@extend_schema(
    summary="Listar cursos destacados",
    description="Devuelve los cursos activos considerados destacados. Requiere autenticación.",
    tags=["Cursos"],
    responses={200: CursoSerializer(many=True)},
)
@action(detail=False, methods=["get"], permission_classes=[IsAuthenticated])
def destacados(self, request):
    cursos = Curso.objects.filter(activo=True)
    serializer = self.get_serializer(cursos, many=True)
    return Response(serializer.data)
```

Observación importante: si esta acción devuelve una lista sin paginar, conviene dejarlo claro en la descripción.

## 21. Documentar la acción inscribirse

La acción `inscribirse` permite que el usuario autenticado se inscriba en un curso concreto.

Ruta generada:

```http
POST /api/cursos/{id}/inscribirse/
```

Características importantes:

* Requiere autenticación.
* No necesita cuerpo de petición.
* Usa el usuario autenticado.
* Busca el perfil `Estudiante` asociado a ese usuario.
* Crea una inscripción si no existe.
* Devuelve error si el usuario no tiene perfil de estudiante.
* Devuelve conflicto si ya estaba inscrito.

Para documentar bien esta acción podemos crear respuestas inline.

Ejemplo:

```python
@extend_schema(
    summary="Inscribirse en un curso",
    description="""
Inscribe al estudiante autenticado en el curso indicado.

No es necesario enviar cuerpo en la petición. La API utiliza el usuario autenticado mediante JWT y busca su perfil de estudiante asociado.
""",
    tags=["Cursos"],
    request=None,
    responses={
        201: OpenApiResponse(
            response=inline_serializer(
                name="InscripcionCreadaResponse",
                fields={
                    "mensaje": serializers.CharField(),
                },
            ),
            description="Inscripción creada correctamente.",
            examples=[
                OpenApiExample(
                    "Inscripción correcta",
                    value={"mensaje": "Inscripción realizada correctamente"},
                )
            ],
        ),
        400: OpenApiResponse(
            response=inline_serializer(
                name="ErrorPerfilEstudianteResponse",
                fields={
                    "error": serializers.CharField(),
                },
            ),
            description="El usuario autenticado no tiene perfil de estudiante.",
            examples=[
                OpenApiExample(
                    "Usuario sin perfil de estudiante",
                    value={"error": "El usuario autenticado no tiene perfil de estudiante"},
                )
            ],
        ),
        409: OpenApiResponse(
            response=inline_serializer(
                name="ErrorInscripcionDuplicadaResponse",
                fields={
                    "error": serializers.CharField(),
                },
            ),
            description="El estudiante ya estaba inscrito en el curso.",
            examples=[
                OpenApiExample(
                    "Inscripción duplicada",
                    value={"error": "El estudiante ya está inscrito"},
                )
            ],
        ),
    },
)
@action(detail=True, methods=["post"], permission_classes=[IsAuthenticated])
def inscribirse(self, request, pk=None):
    curso = self.get_object()

    try:
        estudiante = request.user.estudiante
    except Estudiante.DoesNotExist:
        return Response(
            {"error": "El usuario autenticado no tiene perfil de estudiante"},
            status=status.HTTP_400_BAD_REQUEST
        )

    inscripcion, creada = Inscripcion.objects.get_or_create(
        curso=curso,
        estudiante=estudiante
    )

    if not creada:
        return Response(
            {"error": "El estudiante ya está inscrito"},
            status=status.HTTP_409_CONFLICT
        )

    return Response(
        {"mensaje": "Inscripción realizada correctamente"},
        status=status.HTTP_201_CREATED
    )
```

Esta documentación es mucho más útil que la generada automáticamente, porque explica el comportamiento real del endpoint.

## 22. Documentar ejemplos de petición

Los ejemplos ayudan mucho a entender cómo consumir la API.

Por ejemplo, para crear un curso:

```python
create=extend_schema(
    summary="Crear curso",
    description="Crea un nuevo curso. Requiere autenticación mediante token JWT.",
    tags=["Cursos"],
    request=CursoSerializer,
    responses={201: CursoSerializer},
    examples=[
        OpenApiExample(
            "Crear curso básico de Django",
            value={
                "titulo": "Introducción a Django REST Framework",
                "descripcion": "Curso básico para aprender a crear APIs con Django REST Framework.",
                "categoria": 1,
                "instructor": 1,
                "precio": "49.99",
                "fecha_inicio": "2026-05-10",
                "nivel": "1",
                "activo": True,
                "etiquetas": [1, 2]
            },
            request_only=True,
        ),
        OpenApiExample(
            "Respuesta de curso creado",
            value={
                "id": 1,
                "titulo": "Introducción a Django REST Framework",
                "descripcion": "Curso básico para aprender a crear APIs con Django REST Framework.",
                "categoria": 1,
                "instructor": 1,
                "precio": "49.99",
                "fecha_inicio": "2026-05-10",
                "nivel": "1",
                "activo": True,
                "etiquetas": [1, 2],
                "created_at": "2026-04-28T10:00:00Z",
                "updated_at": "2026-04-28T10:00:00Z"
            },
            response_only=True,
        ),
    ],
)
```

## 23. Documentar errores de validación

En el serializer de curso existe una validación personalizada para impedir precios negativos:

```python
def validate_precio(self, value):
    if value < 0:
        raise serializers.ValidationError("El precio no puede ser negativo")
    return value
```

Conviene documentar ese error.

Ejemplo:

```python
create=extend_schema(
    summary="Crear curso",
    description="Crea un nuevo curso. El precio no puede ser negativo.",
    tags=["Cursos"],
    request=CursoSerializer,
    responses={
        201: CursoSerializer,
        400: OpenApiResponse(
            description="Error de validación en los datos enviados.",
            response=inline_serializer(
                name="CursoValidationErrorResponse",
                fields={
                    "precio": serializers.ListField(
                        child=serializers.CharField()
                    )
                },
            ),
            examples=[
                OpenApiExample(
                    "Precio negativo",
                    value={
                        "precio": ["El precio no puede ser negativo"]
                    },
                )
            ],
        ),
    },
)
```

De esta manera, quien consulte la documentación sabe que si envía un precio negativo recibirá un `400 Bad Request` con un mensaje concreto.

## 24. Documentar autenticación JWT

Los endpoints de JWT son:

```txt
/api/token/
/api/token/refresh/
```

`drf-spectacular` suele detectar estos endpoints, pero es recomendable darles nombre en las rutas:

```python
path('api/token/', TokenObtainPairView.as_view(), name='token_obtain_pair'),
path('api/token/refresh/', TokenRefreshView.as_view(), name='token_refresh'),
```

En Swagger UI normalmente aparecerá un botón **Authorize**. Para usarlo:

1. Hacer una petición `POST /api/token/` con usuario y contraseña.
2. Copiar el valor del token `access`.
3. Pulsar en **Authorize**.
4. Escribir:

```txt
Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6...
```

Es imprescindible escribir la palabra `Bearer`, un espacio y después el token.

## 25. Añadir una descripción general sobre autenticación

Podemos ampliar la descripción general de la API en `SPECTACULAR_SETTINGS`:

```python
SPECTACULAR_SETTINGS = {
    'TITLE': 'API de cursos',
    'DESCRIPTION': '''
API REST para gestionar cursos, categorías, etiquetas, instructores, estudiantes e inscripciones.

Autenticación:
- Los endpoints de lectura pública no requieren token.
- Las operaciones de creación, actualización y eliminación requieren autenticación JWT.
- Para autenticarse, se debe obtener un token en /api/token/.
- Las peticiones protegidas deben incluir la cabecera Authorization: Bearer <token>.
''',
    'VERSION': '1.0.0',
    'SERVE_INCLUDE_SCHEMA': False,
}
```

## 26. Documentar permisos

La documentación debe explicar cuándo un endpoint es público y cuándo requiere autenticación.

En este proyecto hay una lógica habitual:

```python
def get_permissions(self):
    if self.action in ["list", "retrieve"]:
        return [AllowAny()]
    return [IsAuthenticated()]
```

Esto significa:

* `GET /api/cursos/`: público.
* `GET /api/cursos/{id}/`: público.
* `POST /api/cursos/`: requiere autenticación.
* `PUT /api/cursos/{id}/`: requiere autenticación.
* `PATCH /api/cursos/{id}/`: requiere autenticación.
* `DELETE /api/cursos/{id}/`: requiere autenticación.

Aunque spectacular detecte parte de la seguridad, es recomendable explicarlo en la descripción de cada operación.

Ejemplo:

```python
create=extend_schema(
    summary="Crear curso",
    description="Crea un nuevo curso. Requiere autenticación mediante token JWT.",
    tags=["Cursos"],
)
```

## 27. Documentar filtros de forma clara

Aunque spectacular pueda detectar filtros, la documentación automática no siempre es suficientemente didáctica.

Para el filtro de cursos tenemos:

```python
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

Esto permite peticiones como:

```http
GET /api/cursos/?activo=true
GET /api/cursos/?nivel=1
GET /api/cursos/?categoria=2
GET /api/cursos/?precio_min=20
GET /api/cursos/?precio_max=100
GET /api/cursos/?precio_min=20&precio_max=100
```

Ejemplo de documentación manual:

```python
OpenApiParameter(
    name="precio_min",
    type=OpenApiTypes.NUMBER,
    location=OpenApiParameter.QUERY,
    description="Devuelve solo cursos cuyo precio sea mayor o igual que este valor.",
    required=False,
)
```

## 28. Documentar búsqueda

El proyecto permite buscar por título y descripción:

```python
search_fields = ["titulo", "descripcion"]
```

La petición sería:

```http
GET /api/cursos/?search=django
```

Ejemplo de documentación:

```python
OpenApiParameter(
    name="search",
    type=OpenApiTypes.STR,
    location=OpenApiParameter.QUERY,
    description="Busca texto en el título o descripción del curso. Ejemplo: django",
    required=False,
)
```

## 29. Documentar ordenación

El proyecto permite ordenar por:

```python
ordering_fields = ["precio", "fecha_inicio", "titulo", "created_at"]
```

Ejemplos:

```http
GET /api/cursos/?ordering=precio
GET /api/cursos/?ordering=-precio
GET /api/cursos/?ordering=fecha_inicio
GET /api/cursos/?ordering=-created_at
```

Documentación recomendada:

```python
OpenApiParameter(
    name="ordering",
    type=OpenApiTypes.STR,
    location=OpenApiParameter.QUERY,
    description="Ordena los resultados. Valores permitidos: precio, -precio, fecha_inicio, -fecha_inicio, titulo, -titulo, created_at, -created_at.",
    required=False,
)
```

## 30. Documentar serializers de lectura y escritura

En muchas APIs no se usa el mismo serializer para leer que para escribir.

En el proyecto de cursos ocurre algo parecido:

* Para listar cursos se usa `CursoSerializer`.
* Para ver el detalle se usa `CursoDetalleSerializer`.

Esto se controla con:

```python
def get_serializer_class(self):
    if self.action == "retrieve":
        return CursoDetalleSerializer
    return CursoSerializer
```

Conviene documentar explícitamente el serializer de respuesta del detalle:

```python
retrieve=extend_schema(
    summary="Obtener detalle de un curso",
    description="Devuelve el detalle completo de un curso, incluyendo categoría, instructor, etiquetas e inscripciones.",
    tags=["Cursos"],
    responses={200: CursoDetalleSerializer},
)
```

## 31. Documentar SerializerMethodField

En `InscripcionSerializer` existe este campo:

```python
curso_detalle = serializers.SerializerMethodField(read_only=True)
```

Y este método:

```python
def get_curso_detalle(self, obj):
    return {"id": obj.curso_id, "titulo": obj.curso.titulo}
```

A veces spectacular no puede deducir con precisión la estructura de un `SerializerMethodField`. Para mejorarla podemos usar `@extend_schema_field`.

Primero importamos:

```python
from drf_spectacular.utils import extend_schema_field
```

Después podemos definir un serializer auxiliar:

```python
class CursoMiniSerializer(serializers.Serializer):
    id = serializers.IntegerField()
    titulo = serializers.CharField()
```

Y aplicarlo al método:

```python
class InscripcionSerializer(serializers.ModelSerializer):
    estudiante = serializers.PrimaryKeyRelatedField(queryset=Estudiante.objects.all())
    curso = serializers.PrimaryKeyRelatedField(queryset=Curso.objects.all())

    estudiante_detalle = EstudianteSerializer(source="estudiante", read_only=True)
    curso_detalle = serializers.SerializerMethodField(read_only=True)

    @extend_schema_field(CursoMiniSerializer)
    def get_curso_detalle(self, obj):
        return {"id": obj.curso_id, "titulo": obj.curso.titulo}

    class Meta:
        model = Inscripcion
        fields = [
            "id",
            "estudiante", "estudiante_detalle",
            "curso", "curso_detalle",
            "fecha_inscripcion",
            "nota_final",
        ]
        read_only_fields = ["fecha_inscripcion"]
```

Esto hace que la documentación muestre correctamente que `curso_detalle` es un objeto con `id` y `titulo`.

## 32. Documentar campos con help_text

Una forma sencilla de mejorar la documentación automática es usar `help_text` en los modelos o serializers.

Ejemplo en modelo:

```python
codigo = models.CharField(
    max_length=10,
    unique=True,
    help_text="Código corto de la categoría. Ejemplo: DEV, MKT"
)
```

Ejemplo en serializer:

```python
precio = serializers.DecimalField(
    max_digits=6,
    decimal_places=2,
    help_text="Precio del curso en euros. No puede ser negativo."
)
```

Esta información puede aparecer en el schema y ayuda a que la documentación sea más descriptiva.

## 33. Documentar valores posibles de un campo

El modelo `Curso` tiene un campo `nivel` con opciones:

```python
class Nivel(models.TextChoices):
    BASICO = "1", "Nivel Básico"
    INTERMEDIO = "2", "Nivel Intermedio"
    AVANZADO = "3", "Nivel Avanzado"
```

DRF suele detectar las opciones, pero conviene explicarlas en la documentación:

```python
OpenApiParameter(
    name="nivel",
    type=OpenApiTypes.STR,
    location=OpenApiParameter.QUERY,
    description="Nivel del curso: 1 básico, 2 intermedio, 3 avanzado.",
    enum=["1", "2", "3"],
)
```

En los ejemplos también debe usarse el valor real que espera la API:

```json
{
  "nivel": "1"
}
```

No debemos enviar:

```json
{
  "nivel": "Nivel Básico"
}
```

Porque el valor almacenado es `"1"`, no el texto descriptivo.

## 34. Documentar relaciones

En los serializers del proyecto hay varias relaciones representadas por ID:

```python
categoria = serializers.PrimaryKeyRelatedField(...)
instructor = serializers.PrimaryKeyRelatedField(...)
etiquetas = serializers.PrimaryKeyRelatedField(many=True, ...)
```

Esto significa que al crear o modificar un curso no enviamos el objeto completo, sino sus identificadores.

Ejemplo correcto:

```json
{
  "titulo": "Django REST Framework",
  "descripcion": "Curso de API REST",
  "categoria": 1,
  "instructor": 2,
  "precio": "39.99",
  "fecha_inicio": "2026-05-10",
  "nivel": "1",
  "activo": true,
  "etiquetas": [1, 3]
}
```

Ejemplo incorrecto:

```json
{
  "titulo": "Django REST Framework",
  "categoria": {
    "id": 1,
    "nombre": "Desarrollo"
  }
}
```

En la documentación conviene explicar esta diferencia, especialmente para estudiantes que empiezan con APIs.

## 35. Documentar respuestas paginadas

Cuando un endpoint devuelve listas paginadas, la respuesta no es una lista directa. Tiene una estructura con metadatos de paginación.

Ejemplo:

```json
{
  "count": 12,
  "next": "http://127.0.0.1:8000/api/cursos/?page=2",
  "previous": null,
  "results": [
    {
      "id": 1,
      "titulo": "Curso de Django",
      "descripcion": "Introducción a DRF",
      "categoria": 1,
      "instructor": 1,
      "precio": "49.99",
      "fecha_inicio": "2026-05-10",
      "nivel": "1",
      "activo": true,
      "etiquetas": [1, 2],
      "created_at": "2026-04-28T10:00:00Z",
      "updated_at": "2026-04-28T10:00:00Z"
    }
  ]
}
```

El alumnado debe entender que para obtener los cursos hay que acceder a la clave `results`.

## 36. Documentar códigos de estado HTTP

Una buena documentación no solo indica el caso correcto. También debe indicar posibles errores.

Códigos habituales:

* `200 OK`: petición correcta.
* `201 Created`: recurso creado correctamente.
* `204 No Content`: recurso eliminado correctamente.
* `400 Bad Request`: datos inválidos.
* `401 Unauthorized`: no se ha enviado token o el token no es válido.
* `403 Forbidden`: el usuario está autenticado pero no tiene permisos suficientes.
* `404 Not Found`: recurso no encontrado.
* `409 Conflict`: conflicto con el estado actual del recurso.

Ejemplo para `inscribirse`:

```python
responses={
    201: OpenApiResponse(description="Inscripción creada correctamente."),
    400: OpenApiResponse(description="El usuario autenticado no tiene perfil de estudiante."),
    401: OpenApiResponse(description="No se ha enviado un token JWT válido."),
    404: OpenApiResponse(description="Curso no encontrado."),
    409: OpenApiResponse(description="El estudiante ya estaba inscrito en el curso."),
}
```

## 37. Documentar endpoints de creación

Para cada endpoint de creación conviene indicar:

* Qué campos son obligatorios.
* Qué campos son opcionales.
* Qué campos son de solo lectura.
* Qué relaciones se envían como ID.
* Qué errores de validación pueden aparecer.
* Un ejemplo correcto de petición.

Ejemplo para crear una categoría:

```python
create=extend_schema(
    summary="Crear categoría",
    description="Crea una nueva categoría. El campo código debe ser único.",
    tags=["Categorías"],
    examples=[
        OpenApiExample(
            "Crear categoría de desarrollo",
            value={
                "nombre": "Desarrollo web",
                "codigo": "DEV"
            },
            request_only=True,
        )
    ],
)
```

## 38. Documentar endpoints de actualización

Para `PUT` hay que enviar el recurso completo.

Para `PATCH` se pueden enviar solo los campos que queremos modificar.

Ejemplo `PUT`:

```json
{
  "titulo": "Curso avanzado de Django REST Framework",
  "descripcion": "Curso completo de APIs REST con Django.",
  "categoria": 1,
  "instructor": 1,
  "precio": "79.99",
  "fecha_inicio": "2026-06-01",
  "nivel": "3",
  "activo": true,
  "etiquetas": [1, 2]
}
```

Ejemplo `PATCH`:

```json
{
  "precio": "59.99",
  "activo": false
}
```

Conviene explicarlo en la documentación porque es una diferencia importante en APIs REST.

## 39. Documentar eliminación

En una eliminación correcta, DRF normalmente devuelve:

```http
204 No Content
```

Es decir, la respuesta no trae cuerpo.

Ejemplo:

```python
destroy=extend_schema(
    summary="Eliminar curso",
    description="Elimina un curso existente. Requiere autenticación. Si se elimina correctamente, devuelve 204 sin cuerpo de respuesta.",
    tags=["Cursos"],
    responses={
        204: OpenApiResponse(description="Curso eliminado correctamente."),
        401: OpenApiResponse(description="No se ha enviado un token JWT válido."),
        404: OpenApiResponse(description="Curso no encontrado."),
    },
)
```

## 40. Organizar la documentación con tags

Los tags permiten agrupar endpoints.

Ejemplo:

```python
tags=["Cursos"]
```

Recomendación para este proyecto:

* `Cursos`
* `Categorías`
* `Etiquetas`
* `Instructores`
* `Estudiantes`
* `Inscripciones`
* `Autenticación`

Esto hace que Swagger UI sea mucho más legible.

## 41. Personalizar nombres de operaciones

OpenAPI genera internamente un `operationId` para cada operación. Si queremos que tenga un nombre más claro, podemos definirlo:

```python
@extend_schema(
    operation_id="curso_inscribirse",
    summary="Inscribirse en un curso",
    tags=["Cursos"],
)
```

Esto puede ser útil cuando el schema se usa para generar clientes automáticos.

## 42. Documentar endpoints con inline_serializer

A veces un endpoint devuelve una respuesta sencilla y no compensa crear un serializer completo.

Ejemplo:

```json
{
  "mensaje": "Inscripción realizada correctamente"
}
```

Podemos documentarlo con `inline_serializer`:

```python
inline_serializer(
    name="MensajeResponse",
    fields={
        "mensaje": serializers.CharField(),
    },
)
```

Esto es útil para respuestas de confirmación o error simples.

## 43. Crear serializers específicos para documentación

Aunque `inline_serializer` es cómodo, en proyectos grandes puede ser mejor crear serializers explícitos.

Ejemplo:

```python
class MensajeResponseSerializer(serializers.Serializer):
    mensaje = serializers.CharField()

class ErrorResponseSerializer(serializers.Serializer):
    error = serializers.CharField()
```

Y luego usarlos:

```python
responses={
    201: MensajeResponseSerializer,
    400: ErrorResponseSerializer,
    409: ErrorResponseSerializer,
}
```

Ventaja:

* Son reutilizables.
* La documentación queda más ordenada.
* Evitamos repetir estructuras.

## 44. Mejorar la documentación de token JWT

Podemos crear serializers explícitos para documentar el login si usamos vistas propias. Si usamos directamente `TokenObtainPairView`, spectacular normalmente lo documenta automáticamente.

Si se quisiera personalizar, se podría crear una vista propia heredando de `TokenObtainPairView` y decorarla:

```python
from rest_framework_simplejwt.views import TokenObtainPairView

@extend_schema(
    tags=["Autenticación"],
    summary="Obtener token JWT",
    description="Recibe username y password y devuelve un token de acceso y un token de refresco.",
)
class CustomTokenObtainPairView(TokenObtainPairView):
    pass
```

Y en `urls.py`:

```python
path('api/token/', CustomTokenObtainPairView.as_view(), name='token_obtain_pair'),
```

Esto no cambia la lógica del login, solo mejora la documentación.

## 45. Ejemplo completo de imports para views.py

Al principio de `views.py` podríamos tener:

```python
from django_filters.rest_framework import DjangoFilterBackend

from rest_framework import serializers, status, viewsets
from rest_framework.decorators import action
from rest_framework.filters import OrderingFilter, SearchFilter
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.response import Response

from drf_spectacular.utils import (
    extend_schema,
    extend_schema_view,
    OpenApiExample,
    OpenApiParameter,
    OpenApiResponse,
    OpenApiTypes,
    inline_serializer,
)
```

## 46. Ejemplo completo aplicado a CursoViewSet

Este sería un ejemplo integrado para el ViewSet de cursos:

```python
@extend_schema_view(
    list=extend_schema(
        summary="Listar cursos",
        description="""
Devuelve una lista paginada de cursos.

Parámetros disponibles:
- activo: filtra cursos activos o inactivos.
- nivel: filtra por nivel: 1 básico, 2 intermedio, 3 avanzado.
- categoria: filtra por ID de categoría.
- precio_min: precio mínimo.
- precio_max: precio máximo.
- search: busca por título o descripción.
- ordering: ordena por precio, fecha_inicio, titulo o created_at.
""",
        tags=["Cursos"],
        parameters=[
            OpenApiParameter("activo", OpenApiTypes.BOOL, OpenApiParameter.QUERY, description="Filtra por cursos activos o inactivos."),
            OpenApiParameter("nivel", OpenApiTypes.STR, OpenApiParameter.QUERY, description="Nivel: 1 básico, 2 intermedio, 3 avanzado.", enum=["1", "2", "3"]),
            OpenApiParameter("categoria", OpenApiTypes.INT, OpenApiParameter.QUERY, description="ID de la categoría."),
            OpenApiParameter("precio_min", OpenApiTypes.NUMBER, OpenApiParameter.QUERY, description="Precio mínimo."),
            OpenApiParameter("precio_max", OpenApiTypes.NUMBER, OpenApiParameter.QUERY, description="Precio máximo."),
            OpenApiParameter("search", OpenApiTypes.STR, OpenApiParameter.QUERY, description="Texto a buscar en título o descripción."),
            OpenApiParameter("ordering", OpenApiTypes.STR, OpenApiParameter.QUERY, description="Campo de ordenación. Ejemplo: -precio."),
        ],
    ),
    retrieve=extend_schema(
        summary="Obtener detalle de un curso",
        description="Devuelve el detalle completo de un curso, incluyendo datos relacionados.",
        tags=["Cursos"],
        responses={200: CursoDetalleSerializer},
    ),
    create=extend_schema(
        summary="Crear curso",
        description="Crea un curso nuevo. Requiere autenticación JWT.",
        tags=["Cursos"],
        request=CursoSerializer,
        responses={
            201: CursoSerializer,
            400: OpenApiResponse(description="Error de validación en los datos enviados."),
            401: OpenApiResponse(description="No se ha enviado un token JWT válido."),
        },
        examples=[
            OpenApiExample(
                "Curso de ejemplo",
                value={
                    "titulo": "Introducción a Django REST Framework",
                    "descripcion": "Curso básico para crear APIs REST con Django.",
                    "categoria": 1,
                    "instructor": 1,
                    "precio": "49.99",
                    "fecha_inicio": "2026-05-10",
                    "nivel": "1",
                    "activo": True,
                    "etiquetas": [1, 2]
                },
                request_only=True,
            )
        ],
    ),
    destroy=extend_schema(
        summary="Eliminar curso",
        description="Elimina un curso existente. Requiere autenticación JWT.",
        tags=["Cursos"],
        responses={
            204: OpenApiResponse(description="Curso eliminado correctamente."),
            401: OpenApiResponse(description="No se ha enviado un token JWT válido."),
            404: OpenApiResponse(description="Curso no encontrado."),
        },
    ),
)
class CursoViewSet(viewsets.ModelViewSet):
    queryset = (
        Curso.objects.all()
        .select_related("categoria", "instructor")
        .prefetch_related("etiquetas")
    )

    filter_backends = [DjangoFilterBackend, SearchFilter, OrderingFilter]
    filterset_class = CursoFilter
    search_fields = ["titulo", "descripcion"]
    ordering_fields = ["precio", "fecha_inicio", "titulo", "created_at"]
    ordering = ["fecha_inicio"]

    def get_permissions(self):
        if self.action in ["list", "retrieve"]:
            return [AllowAny()]
        return [IsAuthenticated()]

    def get_serializer_class(self):
        if self.action == "retrieve":
            return CursoDetalleSerializer
        return CursoSerializer

    @extend_schema(
        summary="Listar cursos destacados",
        description="Devuelve todos los cursos activos. Requiere autenticación JWT.",
        tags=["Cursos"],
        responses={200: CursoSerializer(many=True)},
    )
    @action(detail=False, methods=["get"], permission_classes=[IsAuthenticated])
    def destacados(self, request):
        cursos = Curso.objects.filter(activo=True)
        serializer = self.get_serializer(cursos, many=True)
        return Response(serializer.data)

    @extend_schema(
        summary="Inscribirse en un curso",
        description="Inscribe al estudiante autenticado en el curso indicado. No requiere cuerpo de petición.",
        tags=["Cursos"],
        request=None,
        responses={
            201: OpenApiResponse(
                response=inline_serializer(
                    name="InscripcionCreadaResponse",
                    fields={"mensaje": serializers.CharField()},
                ),
                description="Inscripción creada correctamente.",
            ),
            400: OpenApiResponse(description="El usuario autenticado no tiene perfil de estudiante."),
            401: OpenApiResponse(description="No se ha enviado un token JWT válido."),
            404: OpenApiResponse(description="Curso no encontrado."),
            409: OpenApiResponse(description="El estudiante ya estaba inscrito."),
        },
    )
    @action(detail=True, methods=["post"], permission_classes=[IsAuthenticated])
    def inscribirse(self, request, pk=None):
        curso = self.get_object()

        try:
            estudiante = request.user.estudiante
        except Estudiante.DoesNotExist:
            return Response(
                {"error": "El usuario autenticado no tiene perfil de estudiante"},
                status=status.HTTP_400_BAD_REQUEST
            )

        inscripcion, creada = Inscripcion.objects.get_or_create(
            curso=curso,
            estudiante=estudiante
        )

        if not creada:
            return Response(
                {"error": "El estudiante ya está inscrito"},
                status=status.HTTP_409_CONFLICT
            )

        return Response(
            {"mensaje": "Inscripción realizada correctamente"},
            status=status.HTTP_201_CREATED
        )
```

## 47. Buenas prácticas para documentar una API REST

Una documentación adecuada debería cumplir estas reglas:

### 47.1. Cada endpoint debe tener un resumen claro

Mal:

```txt
List
```

Bien:

```txt
Listar cursos
```

### 47.2. Cada endpoint importante debe tener una descripción

La descripción debe explicar el propósito del endpoint y cualquier detalle que no sea evidente.

Ejemplo:

```txt
Devuelve una lista paginada de cursos. Permite filtrar por nivel, categoría, estado y rango de precios.
```

### 47.3. Los endpoints protegidos deben indicarlo claramente

Ejemplo:

```txt
Requiere autenticación mediante token JWT.
```

### 47.4. Los parámetros deben tener descripción

No basta con que aparezca `precio_min`. Hay que explicar qué significa.

### 47.5. Deben incluirse ejemplos realistas

Los ejemplos deben parecer datos reales y coherentes con el modelo.

### 47.6. Deben documentarse los errores importantes

Especialmente:

* Validación.
* Autenticación.
* Permisos.
* Recurso no encontrado.
* Conflictos de negocio.

### 47.7. Hay que diferenciar lectura y escritura

Si al crear un curso se envían IDs, pero al leer se devuelven objetos detallados, hay que explicarlo.

### 47.8. La documentación debe actualizarse cuando cambia la API

Si se añade un campo, filtro, endpoint o regla de negocio, la documentación debe revisarse.

## 48. Checklist para el alumnado

Antes de entregar una API documentada, comprobar:

* [ ] El paquete `drf-spectacular` está instalado.
* [ ] `drf_spectacular` está en `INSTALLED_APPS`.
* [ ] `DEFAULT_SCHEMA_CLASS` está configurado en `REST_FRAMEWORK`.
* [ ] Existe `SPECTACULAR_SETTINGS` con título, descripción y versión.
* [ ] Existe la ruta `/api/schema/`.
* [ ] Existe la ruta `/api/docs/`.
* [ ] Swagger UI carga correctamente.
* [ ] Los endpoints aparecen agrupados con tags.
* [ ] Los endpoints principales tienen summary y description.
* [ ] Los endpoints protegidos indican que requieren JWT.
* [ ] Los filtros aparecen documentados.
* [ ] La búsqueda aparece documentada.
* [ ] La ordenación aparece documentada.
* [ ] Las acciones personalizadas están documentadas.
* [ ] Los ejemplos de petición son correctos.
* [ ] Los ejemplos de respuesta son correctos.
* [ ] Los errores importantes están documentados.
* [ ] El schema se puede exportar con `python manage.py spectacular --file schema.yml`.
* [ ] El schema se valida con `python manage.py spectacular --file schema.yml --validate`.

## 49. Errores típicos y soluciones

### Error 1: Swagger no carga

Comprobar que las URLs están bien configuradas:

```python
path('api/schema/', SpectacularAPIView.as_view(), name='schema'),
path('api/docs/', SpectacularSwaggerView.as_view(url_name='schema'), name='swagger-ui'),
```

También comprobar que el servidor está arrancado:

```bash
python manage.py runserver
```

### Error 2: No se reconoce drf_spectacular

Probablemente no está instalado.

Solución:

```bash
pip install drf-spectacular
```

### Error 3: Se instaló el paquete pero Django no arranca

Revisar que en `INSTALLED_APPS` esté escrito:

```python
'drf_spectacular'
```

No:

```python
'drf-spectacular'
```

### Error 4: La documentación sale muy pobre

Añadir:

* `summary`
* `description`
* `tags`
* `parameters`
* `responses`
* `examples`

mediante `@extend_schema` y `@extend_schema_view`.

### Error 5: Una acción personalizada aparece mal documentada

Las acciones con `@action` suelen necesitar documentación manual.

Ejemplo:

```python
@extend_schema(
    summary="Inscribirse en un curso",
    request=None,
    responses={201: OpenApiResponse(description="Inscripción creada correctamente")},
)
@action(detail=True, methods=["post"])
def inscribirse(self, request, pk=None):
    ...
```

### Error 6: No aparece el botón Authorize o no funciona el token

Comprobar que la API usa autenticación JWT en `REST_FRAMEWORK`:

```python
'DEFAULT_AUTHENTICATION_CLASSES': (
    'rest_framework_simplejwt.authentication.JWTAuthentication',
),
```

Al autorizar en Swagger UI, escribir:

```txt
Bearer <token>
```

No escribir solo el token.

### Error 7: El schema muestra mal un SerializerMethodField

Usar `@extend_schema_field` para indicar la estructura del campo.

### Error 8: Los filtros no se entienden

Aunque aparezcan automáticamente, añadir parámetros manuales con `OpenApiParameter` para explicar su significado.


## 50. Resumen final

`drf-spectacular` permite convertir una API Django REST Framework en una API bien documentada siguiendo el estándar OpenAPI 3.

El proceso básico es:

1. Instalar `drf-spectacular`.
2. Añadirlo a `INSTALLED_APPS`.
3. Configurar `DEFAULT_SCHEMA_CLASS`.
4. Añadir `SPECTACULAR_SETTINGS`.
5. Crear rutas para schema y Swagger UI.
6. Probar la documentación en el navegador.
7. Exportar y validar el schema.
8. Mejorar la documentación automática con decoradores.
9. Añadir ejemplos, parámetros, respuestas y errores.
10. Mantener la documentación actualizada junto con la API.

La documentación generada automáticamente es un buen punto de partida, pero una API profesional requiere revisar y enriquecer esa documentación para que sea realmente útil para otros desarrolladores.
