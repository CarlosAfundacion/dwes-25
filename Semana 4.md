# BLOQUE 7 – Autenticación, permisos y control de acceso en la API REST (JWT)

## 1. Objetivo del bloque 

En los bloques anteriores se ha construido una API REST completa:

* CRUD con `ModelViewSet` + `DefaultRouter`.
* Relaciones entre modelos (1:N, N:M, N:M con `through`).
* Filtros, búsqueda, ordenación y paginación.
* Acciones de negocio con `@action`.

Aun así, falta lo que convierte la API en “usable en empresa”:

> La API debe controlar quién puede hacer qué y sobre qué datos.

Este bloque introduce:

1. **Autenticación con JWT**: identifica al usuario (`request.user`).
2. **Permisos**: qué operaciones permite la API según rol/acción.
3. **Control de propiedad**: evitar que un usuario modifique/borrar datos ajenos.
4. **Diferenciación de roles** (al menos):

   * público (sin token) o usuario autenticado
   * admin (`is_staff`)
   * estudiante
   * instructor

---

## 2. Autenticación vs autorización (diferencia imprescindible)

* **Autenticación**: demostrar identidad → “¿quién eres?”

  * Resultado práctico: `request.user` ya no es `AnonymousUser` sino un `User`.
* **Autorización (permisos)**: “¿qué puedes hacer con esta acción y este objeto?”

  * Resultado práctico: DRF permite o bloquea (403) aunque el usuario esté autenticado.

**Regla profesional**

* Autenticación: se configura en `settings.py`.
* Autorización: se implementa en **ViewSets** (`get_permissions`, `permission_classes`) y, si hace falta, en permisos personalizados.
* La “propiedad” se controla con **object-level permissions** (por objeto) y/o filtrando `get_queryset()`.

---

## 3. Por qué no se usan sesiones en una API REST

En una API REST:

* el backend debe ser **stateless**
* cada petición debe incluir credenciales (token)
* hay múltiples clientes (web/móvil/otros servicios)

Por eso se usa autenticación por tokens (JWT) en vez de sesiones.

---

## 4. Autenticación basada en tokens (JWT)

Flujo:

1. Login (credenciales) → devuelve tokens.
2. Cada petición protegida incluye:

```http
Authorization: Bearer <access_token>
```

Ventajas:

* no envías contraseña continuamente
* backend no guarda sesión
* escalable

---

## 5. Qué es JWT (JSON Web Token)

JWT es un token firmado:

```
HEADER.PAYLOAD.SIGNATURE
```

* Está **firmado**: si lo manipulas, se invalida.
* No está pensado para guardar datos sensibles.
* Su payload suele incluir ID de usuario y expiración.

---

## 6. Instalación y configuración de SimpleJWT

### 6.1 Instalación

**Terminal:**

```bash
pip install djangorestframework-simplejwt
```

---

## 7. Configuración básica de JWT

### 7.1 Activar DRF y autenticación JWT

**Archivo: `config/settings.py`**

```python

REST_FRAMEWORK = {
    "DEFAULT_AUTHENTICATION_CLASSES": (
        "rest_framework_simplejwt.authentication.JWTAuthentication",
    ),
}
```

### 7.2 Tiempos recomendados (entorno educativo)

**Archivo: `config/settings.py`**

```python
from datetime import timedelta

SIMPLE_JWT = {
    "ACCESS_TOKEN_LIFETIME": timedelta(minutes=30),
    "REFRESH_TOKEN_LIFETIME": timedelta(days=1),
}
```

---

## 8. Endpoints de autenticación (login y refresh)

Estos endpoints **no van en ViewSets**. Son “infraestructura” de autenticación.

**Archivo: `config/urls.py`**

```python
from django.contrib import admin
from django.urls import path, include
from rest_framework_simplejwt.views import TokenObtainPairView, TokenRefreshView

urlpatterns = [
    path("admin/", admin.site.urls),

    # JWT
    path("api/token/", TokenObtainPairView.as_view(), name="token_obtain_pair"),
    path("api/token/refresh/", TokenRefreshView.as_view(), name="token_refresh"),

    # API de la app
    path("api/", include("cursos.urls")),  # ajusta el nombre de tu app
]
```

---

## 9. Access token y refresh token (cómo se usan)

* **Access token**

  * Se usa para acceder a endpoints protegidos
  * Caduca relativamente rápido (seguridad)
* **Refresh token**

  * Solo sirve para conseguir un nuevo access cuando expira
  * No se usa para acceder a recursos
  * No se inserta en ViewSets

---

## 10. La parte “empresa”: decidir qué endpoints son públicos, de usuario o de admin

### 10.1 Clasificación útil

1. **Catálogo**: `Categoria`, `Etiqueta`

   * lectura suele ser pública
   * escritura solo admin
2. **Identidad/Perfil**: `Estudiante`, `Instructor`

   * un usuario normal no debe tocar perfiles de otros
3. **Contenido**: `Curso`

   * lectura puede ser pública
   * escritura solo instructor propietario o admin
4. **Transacción**: `Inscripcion`

   * un estudiante solo puede ver/crear/borrar sus inscripciones
   * un instructor puede ver inscripciones de sus cursos (y quizá calificar)
   * admin puede todo

### 10.2 Dos preguntas que deciden casi todo

1. **¿Qué peligro hay si cualquiera ejecuta esto?**
   Si la respuesta incluye “borrar/editar datos”, “ver información privada”, “inscribir a otros”, entonces debe restringirse.

2. **¿Existe “propietario” del dato?**
   Si sí: update/delete deben ser “solo propietario (o admin)”.

---

## 11. Vincular actores del dominio a `User` (clave para permisos por rol)

### 11.1 Estudiante ya está vinculado a `User`

Esto ya lo tienes en tu proyecto:

* `Estudiante.usuario = OneToOneField(User, ...)`

Eso permite:

* “soy este estudiante porque `request.user` es mi usuario”.

### 11.2 Instructor debería vincularse a `User` 

Si quieres que “un instructor autenticado gestione sus cursos”, necesitas saber **qué Instructor corresponde a `request.user`**.
Sin ese vínculo, `Instructor` es solo un registro informativo.

**Archivo: `cursos/models.py`** (o tu app)

```python
from django.db import models
from django.contrib.auth.models import User

class Instructor(models.Model):
    usuario = models.OneToOneField(User, on_delete=models.CASCADE, null=True, blank=True)
    nombre = models.CharField(max_length=100)
    bio = models.TextField(verbose_name="Biografía")
    email = models.EmailField()

    def __str__(self):
        return self.nombre
```

* `null=True, blank=True` te permite migrar sin romper datos existentes.
* Lo ideal es que acabe siendo obligatorio (sin null).

**Migraciones (terminal):**

```bash
python manage.py makemigrations
python manage.py migrate
```

---

## 12. Permisos personalizados del proyecto

Crearemos permisos para tus recursos reales:

* Catálogo: escritura solo admin.
* Curso: solo instructor propietario o admin puede modificar/borrar.
* Inscripción: estudiante dueño puede borrar; instructor del curso puede ver; admin todo.

### 12.1 Archivo de permisos

**Archivo: `cursos/permissions.py`** (crear si no existe)

```python
from rest_framework.permissions import BasePermission, SAFE_METHODS

class IsAdminOrReadOnly(BasePermission):
    """
    Lectura para cualquiera. Escritura solo admin (is_staff).
    """
    def has_permission(self, request, view):
        if request.method in SAFE_METHODS:
            return True
        return bool(request.user and request.user.is_staff)


class IsInstructorOwnerOrAdmin(BasePermission):
    """
    Para Curso:
    - Lectura (GET/HEAD/OPTIONS): permitida.
    - Escritura: solo si el usuario es admin o es el instructor propietario del curso.
    Requiere que Curso.instructor tenga Instructor.usuario vinculado.
    """
    def has_object_permission(self, request, view, obj):
        if request.method in SAFE_METHODS:
            return True

        if request.user and request.user.is_staff:
            return True

        # obj es un Curso. Comprobamos si el instructor del curso pertenece al user autenticado.
        instructor_user_id = getattr(getattr(obj, "instructor", None), "usuario_id", None)
        return instructor_user_id == request.user.id


class CanAccessInscripcion(BasePermission):
    """
    Para Inscripcion (modelo intermedio):
    - Admin: todo.
    - Estudiante: puede ver/editar/borrar SOLO sus inscripciones.
    - Instructor: puede ver inscripciones de SUS cursos (si lo decides), pero no borrar inscripciones ajenas.
      (Esto se refuerza también en get_queryset del ViewSet.)
    """
    def has_object_permission(self, request, view, obj):
        if request.user and request.user.is_staff:
            return True

        # obj es una Inscripcion
        estudiante_user_id = getattr(getattr(obj, "estudiante", None), "usuario_id", None)
        if estudiante_user_id == request.user.id:
            return True

        # Si es instructor del curso, permitimos lectura, pero no escritura destructiva
        instructor_user_id = getattr(getattr(getattr(obj, "curso", None), "instructor", None), "usuario_id", None)
        if instructor_user_id == request.user.id:
            return request.method in SAFE_METHODS

        return False
```


---

## 13. Catálogo: Categorías y Etiquetas (lectura libre, escritura admin)

**Archivo: `cursos/views.py`**

```python
from rest_framework.viewsets import ModelViewSet
from .models import Categoria, Etiqueta
from .serializers import CategoriaSerializer, EtiquetaSerializer
from .permissions import IsAdminOrReadOnly

class CategoriaViewSet(ModelViewSet):
    queryset = Categoria.objects.all()
    serializer_class = CategoriaSerializer
    permission_classes = [IsAdminOrReadOnly]


class EtiquetaViewSet(ModelViewSet):
    queryset = Etiqueta.objects.all()
    serializer_class = EtiquetaSerializer
    permission_classes = [IsAdminOrReadOnly]
```

**Teoría (por qué así):**

* `Categoria` y `Etiqueta` son datos de referencia (catálogo).
* En empresa, el catálogo no suele poder editarlo cualquier usuario.
* Permitir lectura abierta es habitual (catálogos públicos), pero la escritura debe estar controlada.

---

## 14. Curso: lectura pública, escritura solo instructor propietario o admin

Aquí juntamos tres ideas:

1. Lectura pública (si lo quieres así): `AllowAny` en `list`/`retrieve`.
2. Escritura: requiere autenticación (`IsAuthenticated`).
3. Update/Delete: además, debe ser el instructor propietario (`IsInstructorOwnerOrAdmin`).
4. Creación: **no confiar en el cliente** para elegir instructor → se asigna con `perform_create`.

### 14.1 ViewSet de cursos

**Archivo: `cursos/views.py`**

```python
from rest_framework.viewsets import ModelViewSet
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.exceptions import ValidationError, PermissionDenied

from .models import Curso, Instructor
from .serializers import CursoSerializer
from .permissions import IsInstructorOwnerOrAdmin

class CursoViewSet(ModelViewSet):
    queryset = Curso.objects.all()
    serializer_class = CursoSerializer

    def get_permissions(self):
        # Lectura pública
        if self.action in ["list", "retrieve"]:
            return [AllowAny()]

        # Escritura: autenticado + (si aplica) control de propiedad
        # Para create/update/delete, añadimos IsInstructorOwnerOrAdmin.
        return [IsAuthenticated(), IsInstructorOwnerOrAdmin()]

    def perform_create(self, serializer):
        """
        Asigna automáticamente el instructor según request.user.
        Evita que el cliente pueda crear cursos "en nombre de otro instructor".
        """
        if self.request.user.is_staff:
            # Si admin quiere elegir instructor, puede hacerlo (si tu serializer lo permite).
            # Si prefieres que admin también asigne por body, no fuerces instructor aquí.
            serializer.save()
            return

        try:
            instructor = Instructor.objects.get(usuario=self.request.user)
        except Instructor.DoesNotExist:
            raise PermissionDenied("Solo un instructor autenticado puede crear cursos.")

        serializer.save(instructor=instructor)
```

**Puntos que suelen confundir (explicación):**

* `get_permissions()` se ejecuta por petición y permite permisos “por acción”.
* `IsInstructorOwnerOrAdmin` es **object-level**: se aplica cuando ya hay un objeto (`retrieve`, `update`, `destroy`).
* En `create`, no hay objeto previo, por eso la seguridad fuerte está en `perform_create` (asignación del instructor).
* Si no asignas instructor automáticamente, un alumno podría “inventarse” un instructor_id y crear cursos de otro.

---

## 15. Inscripción: el estudiante solo puede gestionar “lo suyo”

Tu caso “empresa” típico:

* Un estudiante puede:

  * ver sus inscripciones
  * crear su inscripción (matricularse)
  * borrar su inscripción (cancelar)
* Un instructor puede:

  * ver inscripciones de sus cursos (si lo decides)
* Admin: todo

### 15.1 ViewSet de Inscripciones con `get_queryset()` por rol

**Archivo: `cursos/views.py`**

```python
from rest_framework.viewsets import ModelViewSet
from rest_framework.permissions import IsAuthenticated
from rest_framework.exceptions import PermissionDenied

from .models import Inscripcion, Estudiante, Instructor
from .serializers import InscripcionSerializer
from .permissions import CanAccessInscripcion

class InscripcionViewSet(ModelViewSet):
    serializer_class = InscripcionSerializer
    permission_classes = [IsAuthenticated, CanAccessInscripcion]

    def get_queryset(self):
        """
        Filtrado por rol:
        - admin: todas
        - estudiante: solo sus inscripciones
        - instructor: inscripciones de sus cursos
        """
        user = self.request.user

        if user.is_staff:
            return Inscripcion.objects.all()

        # estudiante autenticado
        estudiante = Estudiante.objects.filter(usuario=user).first()
        if estudiante:
            return Inscripcion.objects.filter(estudiante=estudiante)

        # instructor autenticado
        instructor = Instructor.objects.filter(usuario=user).first()
        if instructor:
            return Inscripcion.objects.filter(curso__instructor=instructor)

        # autenticado pero sin rol (ni estudiante ni instructor)
        return Inscripcion.objects.none()

    def perform_create(self, serializer):
        """
        Crear inscripción: solo un estudiante puede crear SU propia inscripción.
        No se debe aceptar estudiante_id del cliente como fuente de verdad.
        """
        user = self.request.user
        if user.is_staff:
            serializer.save()
            return

        estudiante = Estudiante.objects.filter(usuario=user).first()
        if not estudiante:
            raise PermissionDenied("Solo un estudiante puede crear una inscripción.")

        serializer.save(estudiante=estudiante)
```

**Teoría (por qué además del permiso hace falta `get_queryset`):**

* El permiso por objeto bloquea acciones sobre objetos ajenos.
* `get_queryset()` evita directamente que el usuario “vea” objetos ajenos y suele devolver 404 en accesos por ID.
* En empresa se usa mucho este doble enfoque para evitar enumeración de IDs.

---

## 16. Acción de negocio: `inscribirse` segura (sin `estudiante_id`)

Si en tu acción `inscribirse` aceptas `estudiante_id`, permites “inscribir a otro”.
En un diseño correcto:

* el estudiante se deduce del usuario autenticado (`request.user`)
* nunca se recibe por body un id de otro usuario

### 16.1 Acción dentro de `CursoViewSet`

**Archivo: `cursos/views.py`**

```python
from rest_framework.decorators import action
from rest_framework.permissions import IsAuthenticated
from rest_framework.response import Response
from rest_framework import status
from rest_framework.exceptions import PermissionDenied

from .models import Estudiante, Inscripcion

class CursoViewSet(ModelViewSet):
    # ... (lo anterior)

    @action(detail=True, methods=["post"], permission_classes=[IsAuthenticated])
    def inscribirse(self, request, pk=None):
        """
        Inscribe al estudiante autenticado en este curso.
        No acepta estudiante_id para evitar que un usuario actúe por otros.
        """
        curso = self.get_object()

        if request.user.is_staff:
            return Response(
                {"error": "Acción pensada para estudiantes, no para admin."},
                status=status.HTTP_400_BAD_REQUEST
            )

        estudiante = Estudiante.objects.filter(usuario=request.user).first()
        if not estudiante:
            raise PermissionDenied("Solo un estudiante puede inscribirse.")

        inscripcion, creada = Inscripcion.objects.get_or_create(
            curso=curso,
            estudiante=estudiante
        )

        if not creada:
            return Response({"error": "ya inscrito"}, status=status.HTTP_409_CONFLICT)

        return Response({"mensaje": "inscripción correcta"}, status=status.HTTP_201_CREATED)
```

---

## 18. Serializadores: cómo evitar que el alumno envíe campos que no debe

Aquí suele haber un punto confuso:
si en el serializer de inscripción aparece `estudiante`, un alumno podría intentar enviarlo.

La solución profesional es:

* para creación, usar un serializer de entrada que **no incluya** `estudiante` (lo asigna el servidor)
* o declarar `estudiante` como `read_only=True`

### 17.1 Inscripción: serializer seguro

**Archivo: `cursos/serializers.py`**

```python
from rest_framework import serializers
from .models import Inscripcion

class InscripcionSerializer(serializers.ModelSerializer):
    class Meta:
        model = Inscripcion
        fields = ["id", "estudiante", "curso", "fecha_inscripcion", "nota_final"]
        extra_kwargs = {
            "estudiante": {"read_only": True},  # lo fija el servidor
            "fecha_inscripcion": {"read_only": True},
        }
```

**Comentario importante:**

* Aunque el cliente mande `estudiante` en el JSON, DRF lo ignorará (read_only).
* El `perform_create` del ViewSet es quien asigna el estudiante real.

### 17.2 Curso: serializer recomendado para creación

Si quieres que el instructor no pueda “elegir instructor”, marca `instructor` como read_only.

**Archivo: `cursos/serializers.py`**

```python
from rest_framework import serializers
from .models import Curso

class CursoSerializer(serializers.ModelSerializer):
    class Meta:
        model = Curso
        fields = "__all__"
        extra_kwargs = {
            "instructor": {"read_only": True},  # lo asigna perform_create
        }
```

---

## 18. Práctica

Cada alumno debe adaptar su API REST (temática libre: biblioteca, películas, eventos, cursos, etc.) para añadir **autenticación y control de acceso**, cumpliendo los siguientes puntos:

1. Instalar y configurar **JWT** en el proyecto.
2. Añadir los endpoints de autenticación:

   * `/api/token/`
   * `/api/token/refresh/`
3. Definir permisos para los recursos principales:

   * **Catálogo** (ej.: categorías, géneros, etiquetas):

     * lectura libre
     * escritura solo admin
   * **Recurso principal** (ej.: libro, película, evento, curso):

     * lectura libre o autenticada
     * escritura solo usuario propietario o admin
   * **Relación/acción** (ej.: préstamo, inscripción, reserva):

     * el usuario solo puede ver y gestionar sus propios registros
     * admin puede acceder a todos
4. Modificar la acción de negocio principal para:

   * no aceptar IDs de otros usuarios por body
   * usar siempre `request.user` como identidad del usuario
5. Asegurar que los **serializers**:

   * no permiten enviar campos de usuario/propietario
   * asignan esos campos desde el backend (`perform_create`)

---
