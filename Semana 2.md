# SEMANA 2

---
#  BLOQUE 1 – De vistas manuales a Django REST Framework

---

## 1. Contexto: por qué este bloque existe

Hasta ahora hemos creado endpoints en Django usando:

* `views.py`
* ORM
* `JsonResponse`
* Diccionarios construidos a mano

Este enfoque **funciona**, pero **no escala** y **no es el que se usa en proyectos profesionales**.

### Problemas del enfoque manual

* Mucho código repetido
* Validaciones hechas “a mano”
* Difícil mantener respuestas coherentes
* No hay un estándar claro

En proyectos reales, Django se utiliza junto con **Django REST Framework (DRF)**, que aporta:

* Serialización automática
* Validación de datos
* Vistas especializadas para APIs
* Respuestas HTTP estandarizadas

 **Objetivo del bloque**
Aprender a **reemplazar** las vistas manuales por vistas REST profesionales **sin cambiar los modelos**.

---

## 2. Qué es Django REST Framework (DRF)

Django REST Framework es una **extensión de Django** que facilita la creación de APIs REST.

### Qué añade DRF a Django

* Clases base para vistas REST
* Sistema de serializadores
* Manejo automático de errores
* Navegador de API (útil para desarrollo)

DRF **no sustituye a Django**, sino que:

* Usa los mismos modelos
* Usa el mismo ORM
* Usa el mismo sistema de URLs

 Lo que cambia es **cómo se escriben las vistas**.

---

## 3. Instalación y configuración de DRF

### 3.1 Instalación

Desde el entorno virtual del proyecto:

```bash
pip install djangorestframework
```

### 3.2 Registro en el proyecto

En `settings.py`:

```python
INSTALLED_APPS = [
    ...
    'rest_framework',
]
```
### 3.3 Configuración de CORS (Cross-Origin Resource Sharing)

Este es un paso **obligatorio** para que nuestra API sea profesional. Por seguridad, los navegadores prohíben que una web (ej. una app en React o un simple `index.html`) haga peticiones a un servidor distinto al suyo. Si no configuramos esto, nuestra API solo funcionará en Postman, pero fallará cuando un Frontend intente usarla.

**Instalación:**
```bash
pip install django-cors-headers
```

**Configuración en `settings.py`:**

1.  **Registrar la app:**
    ```python
    INSTALLED_APPS = [
        ...
        'corsheaders',
        'rest_framework',
        ...
    ]
    ```

2.  **Añadir el Middleware (¡Ojo! Debe ir lo más arriba posible):**
    ```python
    MIDDLEWARE = [
        'corsheaders.middleware.CorsMiddleware', # <--- Aquí, antes de CommonMiddleware
        'django.middleware.common.CommonMiddleware',
        ...
    ]
    ```
En Django, el middleware es un componente intermedio que se ejecuta entre la petición HTTP que llega al servidor y la respuesta HTTP que se devuelve al cliente.

3.  **Permitir el acceso (En desarrollo):**
    Añade esta línea al final del archivo para permitir que cualquier aplicación externa se conecte a tu API, en producción cambiaríamos el True por la ip del cliente:
    ```python
    CORS_ALLOW_ALL_ORIGINS = True
    ```


No es necesario tocar nada más de momento.

---

## 4. Primer contacto con una vista DRF

Hasta ahora, una vista típica podía verse así:

```python
from django.http import JsonResponse
from .models import Curso

def cursos_lista(request):
    cursos = Curso.objects.all()
    data = []

    for curso in cursos:
        data.append({
            'id': curso.id,
            'titulo': curso.titulo,
            'precio': curso.precio
        })

    return JsonResponse(data, safe=False)
```

Esto **funciona**, pero es:

* Verboso
* Poco reutilizable
* Difícil de validar

---

## 5. APIView: la base de las vistas REST

DRF introduce la clase `APIView`.

### Características de APIView

* Cada método HTTP es un método de la clase
* Devuelve objetos `Response`
* Gestiona errores HTTP automáticamente

---

## 6. Ejemplo guiado: listado de cursos con APIView

### 6.1 Vista REST

```python
from rest_framework.views import APIView
from rest_framework.response import Response
from .models import Curso

class CursoListAPIView(APIView):
    def get(self, request):
        cursos = Curso.objects.all()

        data = []
        for curso in cursos:
            data.append({
                'id': curso.id,
                'titulo': curso.titulo,
                'precio': curso.precio,
                'nivel': curso.nivel
            })

        return Response(data)
```

### 6.2 URL asociada

```python
from django.urls import path
from cursos.views import CursoListAPIView

urlpatterns = [
    path('api/cursos/', CursoListAPIView.as_view()),
]
```

### 6.3 Qué hemos ganado

* Uso de DRF
* Respuesta REST estándar
* Preparación para serializadores

 **Importante**:
Seguimos construyendo JSON manualmente **a propósito**.
En el siguiente bloque veremos por qué esto no es suficiente.

---

## 7. Conectar DRF con modelos reales (ORM)

DRF **no cambia nada del ORM**.

Esto sigue siendo válido:

```python
Curso.objects.all()
Curso.objects.get(id=1)
Curso.objects.filter(activo=True)
```

Lo único que cambia es **cómo devolvemos la respuesta**.

---

## 8. Conclusión del bloque

En este bloque:

* Hemos pasado de vistas manuales a vistas REST
* Hemos integrado DRF en el proyecto
* Hemos creado un endpoint REST funcional
* Hemos comprobado que el ORM sigue siendo el mismo

 **Problema pendiente (intencionado):**

* Seguimos construyendo JSON a mano
   Esto lo resolveremos con **serializadores** en el Bloque 2.

---

#  PRÁCTICA

**Objetivo**
Integrar Django REST Framework y crear **dos endpoints GET y uno POST** equivalentes a los del sprint anterior de Django, utilizando **APIView**.

---

### Pasos

1. Instala Django REST Framework:

   ```bash
   pip install djangorestframework
   ```
2. Añade `"rest_framework"` a `INSTALLED_APPS`.
3. Instala y configura **CORS** para permitir peticiones desde un frontend.
4. Elige un **recurso principal** de tu proyecto
   (por ejemplo: `Libro`, `Producto`, `Equipo`, `Curso`, etc.).
5. Crea las vistas usando **APIView**:

   * GET lista → devuelve todos los elementos del recurso
   * GET detalle → devuelve un único elemento por `id`
   * POST → crea un nuevo elemento
6. Define las rutas en `urls.py`:

   * `/api/<recurso>/` → GET (lista) y POST (crear)
   * `/api/<recurso>/<int:pk>/` → GET (detalle)
7. Prueba todos los endpoints en **Postman**:

   * Comprueba códigos de estado
   * Comprueba estructura del JSON

---

### Restricciones

*  No copiar el ejemplo visto en clase
*  Adaptar la práctica a tu dominio
  (biblioteca, tienda, liga, etc.)

---

#  BLOQUE 2 – Serializadores y validación de datos en Django REST Framework

---

## 1. Por qué necesitamos serializadores

En el Bloque 1 hemos creado endpoints REST usando Django REST Framework (`APIView`), pero **seguimos construyendo el JSON a mano**.

Ejemplo típico que ya sabemos hacer:

```python
data = {
    'id': curso.id,
    'titulo': curso.titulo,
    'precio': curso.precio
}
```

Este enfoque presenta varios problemas:

* Mucho código repetido
* Cada endpoint construye el JSON a su manera
* No hay validación automática
* Difícil de mantener si el modelo cambia

En proyectos reales **esto no se hace así**.

---

## 2. Qué es un serializer

Un **serializer** es una clase que se encarga de:

* Convertir **objetos Python (modelos)** en **JSON**
* Convertir **JSON** en **objetos Python**
* Validar los datos de entrada

Es decir, actúa como **puente** entre:

```
Modelo Django ↔ Serializer ↔ JSON
```

Para usar serializers tendremos que crear el archivo `serializers.py` dentro de la carpeta de nuestra app (la que contiene `models.py`, `views.py`y demás)

**Idea clave**

> El serializer define **qué datos salen** y **qué datos entran** en la API.

---

## 3. Tipos de serializadores en DRF

### 3.1 Serializer (manual)

Permite definir campos uno a uno.

Ejemplo:

```python
from rest_framework import serializers

class CursoSerializer(serializers.Serializer):
    titulo = serializers.CharField()
    precio = serializers.DecimalField(max_digits=6, decimal_places=2)
```

**No lo usaremos de momento**, porque:

* Requiere mucho código
* Repite información que ya está en el modelo

---

### 3.2 ModelSerializer (el que usaremos)

`ModelSerializer` genera automáticamente los campos a partir del modelo.

Ejemplo:

```python
from rest_framework import serializers
from .models import Curso

class CursoSerializer(serializers.ModelSerializer):
    class Meta:
        model = Curso
        fields = '__all__'
```

 Es el más usado
 Es el más productivo
 Es el estándar en empresa

---

## 4. Crear el primer serializer (ejemplo guiado)

### 4.1 Archivo `serializers.py`

En la app, crea el archivo `serializers.py`.

```python
from rest_framework import serializers
from .models import Curso

class CursoSerializer(serializers.ModelSerializer):
    class Meta:
        model = Curso
        fields = ['id', 'titulo', 'precio', 'nivel']
```

No todos los campos se comportan igual.

*   **`read_only=True`**: El campo se envía al cliente (GET), pero si el cliente lo envía en un POST, Django lo ignora. Útil para fechas de creación o IDs.
*   **`write_only=True`**: El campo se puede enviar para crear/editar (POST/PUT), pero **nunca** se devolverá en el JSON de respuesta. Es vital para contraseñas o datos sensibles.

**Código funcional:**
```python
class CursoSerializer(serializers.ModelSerializer):
    class Meta:
        model = Curso
        fields = ['id', 'titulo', 'precio', 'created_at']
        extra_kwargs = {
            'id': {'read_only': True},
            'created_at': {'read_only': True},
            # Si tuviéramos una contraseña de acceso al curso:
            # 'password': {'write_only': True}
        }
```

 **Buena práctica**
No usar siempre `__all__`. Expón solo lo necesario.

---

## 5. Serializar datos (GET)

### 5.1 Serializar una lista

En la vista:

```python
from rest_framework.views import APIView
from rest_framework.response import Response
from .models import Curso
from .serializers import CursoSerializer

class CursoListAPIView(APIView):
    def get(self, request):
        cursos = Curso.objects.all()
        serializer = CursoSerializer(cursos, many=True)# many = true indica que es una lista, no un único elemento
        return Response(serializer.data)
```

### 5.2 Qué hace DRF aquí

* Convierte cada objeto `Curso` en JSON
* Devuelve una lista automáticamente
* Elimina el JSON manual

---

## 6. Serializar un objeto individual (GET por id)
Añadir a `urls.py`:

```python
path('api/cursos/<int:pk>/', CursoDetailAPIView.as_view()),
```

```python
class CursoDetailAPIView(APIView):
    def get(self, request, pk):
        curso = Curso.objects.get(pk=pk)
        serializer = CursoSerializer(curso)
        return Response(serializer.data)
```

 **Resultado**

> Mismo resultado que antes, pero con menos código y más control.

---

## 7. Deserializar datos (POST)

Ahora el flujo se invierte:

```
JSON → Serializer → Modelo → Base de datos
```

### 7.1 Crear un objeto con serializer

Añadir a `urls.py`:

```python
path('api/cursos/crear/', CursoCreateAPIView.as_view()),
```
En `serializers.py`, cambiar la asignación de fields a `'__all__'`

```python

class CursoCreateAPIView(APIView):
    def post(self, request):
        serializer = CursoSerializer(data=request.data)

        if serializer.is_valid():
            serializer.save()
            return Response(serializer.data, status=201)

        return Response(serializer.errors, status=400)
```

---

## 8. Validación automática

DRF valida automáticamente:

* Tipos de datos
* Campos obligatorios
* Longitud máxima
* Valores incorrectos

Ejemplo:

* Enviar un string donde se espera un número
* Omitir un campo obligatorio

 DRF devuelve:

* Código **400**
* Mensajes claros de error en JSON

---

## 9. Validación personalizada

Cuando las reglas de negocio no están en el modelo.

### 9.1 Validar un campo

```python
class CursoSerializer(serializers.ModelSerializer):
    class Meta:
        model = Curso
        fields = ['titulo', 'precio']

    def validate_precio(self, value):
        if value < 0:
            raise serializers.ValidationError("El precio no puede ser negativo")
        return value
```

---

### 9.2 Validación global

```python
def validate(self, data):
    if data['precio'] == 0 and data['nivel'] == 'AVZ':
        raise serializers.ValidationError("Un curso avanzado no puede ser gratuito")
    return data
```

---

## 10. Qué NO hace un serializer

Un serializer:

*  No accede directamente a la BD
*  No decide qué endpoint existe
*  No controla permisos

 Solo se encarga de **datos y validación**.

---

## 11. Conclusión del bloque

En este bloque hemos aprendido a:

* Usar `ModelSerializer`
* Serializar listas y objetos
* Deserializar datos
* Validar automáticamente
* Validar reglas de negocio

**Resultado clave**

> Nuestra API ya no construye JSON a mano.

---

# PRÁCTICA

**Objetivo**: dejar de construir JSON manual y añadir GET lista + GET detalle + POST usando serializadores.

**Pasos**

1. Crea `serializers.py` en tu app.
2. Crea al menos **2** `ModelSerializer` para dos modelos (p.ej. `Libro`, `Autor`).
3. Elige **un recurso principal** y crea:

   * GET lista: `Serializer(queryset, many=True).data`
   * GET detalle: `Serializer(obj).data`
   * POST crear: `Serializer(data=request.data)` + `is_valid()` + `save()`
4. En Postman prueba:

   * POST correcto (201)
   * POST incorrecto (400) forzando errores (campo obligatorio ausente, tipo incorrecto…)
     
### Restricciones

* No copiar el ejemplo
* Adapta a tu dominio

---


#  BLOQUE 3 – CRUD completo con vistas genéricas y ViewSets en DRF

---

## 1. Por qué este bloque es necesario

En el Bloque 2 ya hemos conseguido algo muy importante:

* Serializadores funcionando
* GET y POST correctos
* Validaciones automáticas

Sin embargo, nuestras vistas siguen teniendo este problema:

* Mucho código repetido
* Un método por operación
* Difícil de escalar cuando hay muchos recursos

Ejemplo típico que ya hemos escrito varias veces:

```python
class CursoListAPIView(APIView):
    def get(self, request):
        cursos = Curso.objects.all()
        serializer = CursoSerializer(cursos, many=True)
        return Response(serializer.data)

    def post(self, request):
        serializer = CursoSerializer(data=request.data)
        if serializer.is_valid():
            serializer.save()
            return Response(serializer.data, status=201)
        return Response(serializer.errors, status=400)
```

 **Esto funciona**, pero **DRF nos permite hacerlo mejor**.

---

## 2. Qué son las vistas genéricas en DRF

DRF proporciona **vistas genéricas** que ya implementan la lógica CRUD más común.

Estas vistas:

* Ya saben usar serializadores
* Ya saben acceder al ORM
* Ya saben devolver códigos HTTP correctos

Tú solo tienes que indicar:

* Qué modelo
* Qué serializer

---

## 3. CRUD con vistas genéricas

### 3.1 Listar y crear (`ListCreateAPIView`)

Esta vista combina:

* `GET` → listar
* `POST` → crear

### Ejemplo: cursos

```python
from rest_framework.generics import ListCreateAPIView
from .models import Curso
from .serializers import CursoSerializer

class CursoListCreateAPIView(ListCreateAPIView):
    queryset = Curso.objects.all()
    serializer_class = CursoSerializer
```

 **Observa**
No hay métodos `get()` ni `post()`.
DRF se encarga de todo.

---

### 3.2 Obtener, actualizar y borrar (`RetrieveUpdateDestroyAPIView`)

Esta vista cubre:

* `GET /{id}`
* `PUT /{id}`
* `PATCH /{id}`
* `DELETE /{id}`

```python
from rest_framework.generics import RetrieveUpdateDestroyAPIView

class CursoDetailAPIView(RetrieveUpdateDestroyAPIView):
    queryset = Curso.objects.all()
    serializer_class = CursoSerializer
```

---

### 3.3 URLs asociadas

```python
from django.urls import path
from .views import CursoListCreateAPIView, CursoDetailAPIView

urlpatterns = [
    path('api/cursos/', CursoListCreateAPIView.as_view()),
    path('api/cursos/<int:pk>/', CursoDetailAPIView.as_view()),
]
```

 **Resultado**

> CRUD completo con muy poco código.

---

## 4. Qué ganamos usando vistas genéricas

* Menos código
* Menos errores
* Código más legible
* Patrón estándar de empresa

 **Este es el enfoque más común en APIs REST con DRF.**

---

## 5. ViewSets: un paso más hacia la limpieza

Aunque las vistas genéricas ya son muy potentes, seguimos teniendo:

* Dos clases por recurso
* Muchas rutas repetidas

DRF ofrece una solución mejor: **ViewSets**.

---

## 6. Qué es un ViewSet

Un **ViewSet** agrupa todas las operaciones CRUD de un recurso en **una sola clase**.

Un `ModelViewSet` incluye:

* list
* retrieve
* create
* update
* partial_update
* destroy

---

## 7. Ejemplo completo con `ModelViewSet`

```python
from rest_framework.viewsets import ModelViewSet
from .models import Curso
from .serializers import CursoSerializer

class CursoViewSet(ModelViewSet):
    queryset = Curso.objects.all()
    serializer_class = CursoSerializer
```

 **Con esto ya tenemos todo el CRUD**.

---

## 8. Routers: URLs automáticas

Los routers generan automáticamente las rutas REST.

### 8.1 Definir el router

```python
from rest_framework.routers import DefaultRouter
from .views import CursoViewSet

router = DefaultRouter()
router.register(r'cursos', CursoViewSet)
```

---

### 8.2 URLs finales

```python
from django.urls import path, include
from .router import router

urlpatterns = [
    path('api/', include(router.urls)),
]
```

Esto genera automáticamente:

| Método | URL               |
| ------ | ----------------- |
| GET    | /api/cursos/      |
| POST   | /api/cursos/      |
| GET    | /api/cursos/{id}/ |
| PUT    | /api/cursos/{id}/ |
| PATCH  | /api/cursos/{id}/ |
| DELETE | /api/cursos/{id}/ |

---

## 9. Comparativa final

| Enfoque   | Código | Escalabilidad |
| --------- | ------ | ------------- |
| APIView   | Mucho  | Baja          |
| Genéricas | Medio  | Alta          |
| ViewSet   | Poco   | Muy alta      |

 **En empresa**, lo más habitual es:

* `ModelViewSet` + routers

---



## 10. Buenas prácticas en este bloque

* Un ViewSet por recurso
* Serializadores separados
* URLs limpias
* No duplicar lógica
* No mezclar responsabilidades

---

## 11. Conclusión del bloque

En este bloque hemos aprendido a:

* Crear CRUD completos con vistas genéricas
* Simplificar aún más con ViewSets
* Usar routers para generar URLs REST
* Escribir código limpio y profesional

 **Resultado clave**

> Nuestra API ya es completamente CRUD y escalable.

---

# PRÁCTICA

**Objetivo**: implementar un CRUD completo de un recurso usando **ModelViewSet + Router**.

**Pasos**

1. Elige **dos recursos** (p.ej. `Producto`, `Libro`, `Partido`…).
2. Asegúrate de tener su `ModelSerializer` funcionando (Bloque 2).
3. Crea `views.py` con un `ModelViewSet`:

   * `queryset = Modelo.objects.all()`
   * `serializer_class = TuSerializer`
4. En `urls.py`:

   * crea `DefaultRouter()`
   * `router.register("recurso", TuViewSet, basename="recurso")`
   * incluye `path("api/", include(router.urls))`
5. Prueba en Postman:

   * `GET /api/recurso/`
   * `POST /api/recurso/`
   * `GET /api/recurso/1/`
   * `PATCH /api/recurso/1/`
   * `DELETE /api/recurso/1/`
     


---





