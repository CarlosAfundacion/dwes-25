# BLOQUE 7 – Autenticación y permisos en la API REST con JWT

---

## 1. Contexto y objetivo del bloque

Hasta este punto del curso, la API REST de cursos ya es técnicamente completa:

* Modelos con relaciones (Curso, Categoría, Estudiante, Inscripción).
* CRUD completo mediante `ModelViewSet`.
* Filtros, búsqueda, ordenación y paginación.
* Endpoints de negocio (`@action`) como `inscribirse`.

Sin embargo, existe un problema crítico:

> **La API no distingue quién realiza las peticiones ni qué permisos tiene.**

Esto implica que:

* Cualquiera puede crear, modificar o borrar cursos.
* Cualquiera puede ejecutar acciones de negocio como inscribirse.
* No existe ningún nivel de seguridad.

En un entorno profesional, una API en este estado **no es aceptable**.

### Objetivo del bloque 7

Incorporar un sistema de **autenticación y permisos** que permita:

1. Identificar al usuario que realiza una petición.
2. Restringir operaciones sensibles.
3. Mantener endpoints públicos cuando tenga sentido.
4. Proteger acciones de negocio.
5. Hacer que la API sea realista a nivel “empresa junior”.

---

## 2. Por qué una API REST no usa sesiones

En Django tradicional (con HTML y plantillas):

* Se usan sesiones.
* El servidor mantiene estado.
* El navegador es el cliente principal.

En una API REST:

* Hay múltiples tipos de clientes.
* El servidor debe ser **stateless**.
* Cada petición debe contener toda la información necesaria.

Por este motivo, en APIs REST **no se usan sesiones**, sino **tokens**.

---

## 3. Autenticación basada en tokens

### Idea fundamental

1. El usuario se autentica una vez.
2. El servidor devuelve un token.
3. El cliente envía ese token en cada petición protegida.

Cabecera estándar:

```http
Authorization: Bearer <token>
```

Ventajas:

* No se envía la contraseña constantemente.
* El servidor no guarda estado.
* El sistema escala bien.

---

## 4. Qué es JWT (JSON Web Token)

JWT es un estándar para representar información de forma segura.

Un JWT:

* Es un string.
* Está firmado.
* Es autocontenido.

Estructura conceptual:

```
HEADER.PAYLOAD.SIGNATURE
```

### Información típica que contiene

* ID del usuario.
* Fecha de expiración.
* Metadatos mínimos.

### Información que **nunca** debe contener

* Contraseñas.
* Datos sensibles.
* Información privada.

> El token **no cifra** la información, solo la firma.

---

## 5. JWT en Django REST Framework

En Django REST Framework **no se implementa JWT a mano**.
Se usa una librería estándar:

* `djangorestframework-simplejwt`

Esta librería:

* Se integra con DRF.
* Usa el modelo `User` de Django.
* Proporciona endpoints de autenticación.
* Gestiona expiración y validación de tokens.

---

## 6. Instalación de Simple JWT

**Archivo implicado:** entorno virtual del proyecto

```bash
pip install djangorestframework-simplejwt
```

No se crean nuevas apps ni modelos.

---

## 7. Configuración global de JWT

### 7.1. Configuración de DRF

**Archivo:** `config/settings.py`

Configurar autenticación JWT:

```python
REST_FRAMEWORK = {
    'DEFAULT_AUTHENTICATION_CLASSES': (
        'rest_framework_simplejwt.authentication.JWTAuthentication',
    ),
}
```

A partir de aquí:

* DRF buscará un JWT en la cabecera `Authorization`.
* Si un endpoint exige autenticación, el token será validado automáticamente.
* El usuario autenticado estará disponible en `request.user`.

---

### 7.2. Configuración recomendada de expiración

**Archivo:** `config/settings.py`

```python
from datetime import timedelta

SIMPLE_JWT = {
    'ACCESS_TOKEN_LIFETIME': timedelta(minutes=30),
    'REFRESH_TOKEN_LIFETIME': timedelta(days=7),
}
```

---

## 8. Endpoints de autenticación

Simple JWT ya proporciona las vistas necesarias.

### Configuración de URLs

**Archivo:** `config/urls.py`

```python
from django.urls import path
from rest_framework_simplejwt.views import (
    TokenObtainPairView,
    TokenRefreshView,
)

urlpatterns = [
    ...
    path('api/token/', TokenObtainPairView.as_view()),
    path('api/token/refresh/', TokenRefreshView.as_view()),
]
```

### Función de cada endpoint

* `POST /api/token/`

  * Autenticación inicial.
  * Devuelve `access` y `refresh`.

* `POST /api/token/refresh/`

  * Devuelve un nuevo `access`.

---

## 9. Usuarios del sistema (base de la autenticación)

JWT se basa en el modelo `User` de Django.

### Crear superusuario

```bash
python manage.py createsuperuser
```

### Crear usuario normal (ejemplo)

```python
from django.contrib.auth.models import User

User.objects.create_user(
    username="alumno1",
    password="1234"
)
```

Estos usuarios son los que se autenticarán para operar con la API.

---

## 10. Uso del token en peticiones autenticadas

En todas las peticiones protegidas se debe enviar:

```http
Authorization: Bearer <access_token>
```

Comportamiento:

* Token válido → petición aceptada.
* Token ausente o inválido → `401 Unauthorized`.

DRF gestiona automáticamente:

* Decodificación.
* Validación.
* Asociación del usuario (`request.user`).

---

## 11. Permisos en DRF: concepto clave

Autenticación responde a:

> ¿Quién eres?

Permisos responden a:

> ¿Qué puedes hacer?

---

## 12. Permiso básico: `IsAuthenticated`

### Aplicación a toda la API de cursos

**Archivo:** `cursos/views.py`

```python
from rest_framework.permissions import IsAuthenticated
from rest_framework.viewsets import ModelViewSet
from .models import Curso
from .serializers import CursoSerializer

class CursoViewSet(ModelViewSet):
    queryset = Curso.objects.all()
    serializer_class = CursoSerializer
    permission_classes = [IsAuthenticated]
```

Efecto:

* Todas las operaciones requieren autenticación.
* Sin token → `401`.

Este enfoque es válido para APIs privadas, pero demasiado restrictivo para nuestro caso.

---

## 13. Endpoints públicos y privados en la API de cursos

En la API de cursos es razonable que:

* Listar cursos sea público.
* Ver detalle de un curso sea público.
* Crear, editar y borrar cursos sea privado.

### Implementación profesional con `get_permissions()`

**Archivo:** `cursos/views.py`

```python
from rest_framework.permissions import AllowAny, IsAuthenticated

class CursoViewSet(ModelViewSet):
    queryset = Curso.objects.all()
    serializer_class = CursoSerializer

    def get_permissions(self):
        if self.action in ['list', 'retrieve']:
            return [AllowAny()]
        return [IsAuthenticated()]
```

### Resultado práctico

| Acción        | Endpoint          | Requiere token |
| ------------- | ----------------- | -------------- |
| Listar cursos | GET /cursos/      | No             |
| Detalle curso | GET /cursos/{id}/ | No             |
| Crear curso   | POST /cursos/     | Sí             |
| Editar curso  | PUT/PATCH         | Sí             |
| Borrar curso  | DELETE            | Sí             |

Este patrón es **estándar en empresa**.

---

## 14. JWT y acciones de negocio: `inscribirse`

Las acciones de negocio **también deben protegerse**.

### Ejemplo: inscripción a un curso

**Archivo:** `cursos/views.py`

```python
from rest_framework.decorators import action
from rest_framework.permissions import IsAuthenticated
from rest_framework.response import Response
from rest_framework import status
from .models import Inscripcion

class CursoViewSet(ModelViewSet):
    ...

    @action(
        detail=True,
        methods=['post'],
        permission_classes=[IsAuthenticated]
    )
    def inscribirse(self, request, pk=None):
        curso = self.get_object()
        estudiante = request.user.estudiante

        inscripcion, creada = Inscripcion.objects.get_or_create(
            curso=curso,
            estudiante=estudiante
        )

        if not creada:
            return Response(
                {'error': 'El estudiante ya está inscrito'},
                status=status.HTTP_409_CONFLICT
            )

        return Response(
            {'mensaje': 'Inscripción realizada correctamente'},
            status=status.HTTP_201_CREATED
        )
```

### Conceptos importantes

* `request.user` es el usuario autenticado.
* No se envía el ID del estudiante desde fuera.
* La inscripción queda ligada al usuario autenticado.
* Se evita manipulación externa de IDs.

---

## 15. Errores HTTP más habituales

### 401 Unauthorized

* No se envía token.
* Token inválido o expirado.

### 403 Forbidden

* Usuario autenticado.
* No tiene permiso para esa acción.

La diferencia es clave en APIs profesionales.

---

## 16. Buenas prácticas aplicadas al proyecto de cursos

* JWT estándar.
* Endpoints públicos bien definidos.
* Escritura siempre protegida.
* Acciones de negocio protegidas.
* Uso de `request.user` en lugar de IDs externos.
* Permisos definidos en vistas, no en serializadores.

---

## 17. Trabajo autónomo del alumnado

Cada alumno debe adaptar este bloque a su temática:

1. Configurar JWT.
2. Añadir endpoints de autenticación.
3. Crear usuarios.
4. Definir qué endpoints son públicos.
5. Proteger operaciones de escritura.
6. Proteger al menos una acción de negocio.

---

## 18. Checklist de evaluación

El proyecto cumple el bloque 7 si:

* JWT funciona correctamente.
* Se distingue acceso público y privado.
* Las operaciones sensibles están protegidas.
* Las acciones de negocio requieren autenticación.
* La API responde con códigos coherentes.

---

## 19. Función del bloque en el curso

Este bloque:

* Cierra técnicamente la API.
* Eleva el proyecto a nivel profesional.
* Deja al alumnado preparado para:

  * Integración con frontend.
  * Despliegue.
  * Trabajo en entornos reales.

---

