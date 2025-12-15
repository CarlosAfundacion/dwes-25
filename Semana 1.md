
# Semana 1: Desarrollo Backend con Django - Modelado de Datos

## 1. Objetivo de la semana

Hasta ahora hemos creado tablas usando `CREATE TABLE` en SQL y hemos realizado peticiones básicas. En Django, **los modelos son el corazón de la aplicación**: todo lo que hagamos después (vistas, plantillas, API REST, autenticación…) depende de un modelado correcto.

**En esta semana aprenderás a:**
1.  Aplicar el enfoque **code-first**: diseñar la BD con clases Python, sin tocar SQL.
2.  Crear relaciones profesionales (1:N, N:M, 1:1) y modelos intermedios.
3.  Gestionar cambios en la BD mediante **migraciones**.
4.  Usar el **ORM** para interactuar con los datos y el **Admin** para gestionarlos.

---

## 2. Preparación del entorno

Recordemos cómo iniciar un proyecto Django correctamente:

```bash
# 1. Crear entorno virtual (aislar librerías)
# (Saltar si usas PyCharm ya que lo genera automáticamente)
python -m venv .venv

# 2. Activar entorno (Windows)
.venv\Scripts\activate
# (Mac/Linux: source .venv/bin/activate)

# 3. Instalar Django (Si no lo hemos hecho ya)
pip install django

# 4. Crear el proyecto (la carpeta configuración)
# El punto final (.) evita duplicidad de carpetas
python -m django startproject config .

# 5. Crear aplicación (donde irá el código)
python manage.py startapp cursos

# 6. IMPORTANTE: Registrar la app en config/settings.py
# INSTALLED_APPS = [ ... 'cursos', ]
```

---

## 3. Filosofía *Code-First* en Django

En Django **nunca escribimos SQL para crear tablas**. En su lugar, definimos la estructura mediante **clases Python** en el archivo `models.py`.

### Flujo profesional de trabajo
1.  **Diseñar modelos:** Escribes clases en `models.py`.
2.  **Generar la "receta" (Migrations):** Ejecutas `python manage.py makemigrations`. Django detecta cambios y crea un archivo de instrucciones.
3.  **Aplicar a la BD (Migrate):** Ejecutas `python manage.py migrate`. Django ejecuta el SQL necesario por ti.
4.  **Gestionar datos:** Usas el panel de administración o la *shell*.

> **Ventaja:** Este enfoque permite que el proyecto sea **incremental**. Si mañana necesitas añadir un campo "precio" al curso, solo lo añades en Python y creas una nueva migración.

---

## 4. Definición de modelos: Campos básicos

Cada atributo de la clase será una columna en la tabla. Django traduce el tipo de Python al tipo de SQL adecuado (VARCHAR, INT, BOOLEAN, etc.).

### Tipos de campos esenciales

| Propósito | Campo Django | Uso en nuestro proyecto |
| :--- | :--- | :--- |
| **Texto corto** | `CharField(max_length=X)` | Nombres, títulos, códigos. |
| **Texto largo** | `TextField()` | Descripciones, contenidos extensos. |
| **Enteros** | `IntegerField()` | Duración en horas, cupo, notas enteras. |
| **Decimales** | `DecimalField(max_digits, decimal_places)` | Precios (ej: 99.99), notas exactas. |
| **Fechas** | `DateField()` | Fecha de inicio, cumpleaños. |
| **Fecha/Hora** | `DateTimeField()` | Auditoría (cuándo se creó el registro). |
| **Booleanos** | `BooleanField()` | ¿Está activo? ¿Es gratuito? |
| **Correos** | `EmailField()` | Contacto del profesor o usuario. |
| **Opciones** | `CharField(choices=LISTA)` | Niveles (Básico, Medio, Avanzado). |

### Choices (Enumeraciones)
Para listas desplegables fijas en código (no requieren una tabla extra en BD).
```python
class Nivel(models.TextChoices):
    BASICO = 'BAS', 'Básico'
    MEDIO = 'MED', 'Intermedio'
    AVANZADO = 'ADV', 'Avanzado'

nivel = models.CharField(choices=Nivel.choices, default=Nivel.BASICO)
```

### Atributos de configuración comunes
Podemos modificar el comportamiento de cada columna con estos argumentos:

*   `null=True`: La base de datos acepta valores `NULL`.
*   `blank=True`: El formulario de validación (y el Admin) permite dejarlo vacío.
*   `default=valor`: Valor por defecto si no se especifica nada.
*   `unique=True`: No permite valores duplicados en esa columna (ej: DNI, email).
*   `verbose_name="..."`: Nombre legible para humanos en el Admin.
*   `help_text="..."`: Texto de ayuda pequeño que aparece bajo el *input*.

### Campos de auditoría (Automáticos)
Son muy útiles para saber la "edad" de un dato. Se usan en casi todos los proyectos reales:
```python
created_at = models.DateTimeField(auto_now_add=True) # Se fija al crear el registro
updated_at = models.DateTimeField(auto_now=True)     # Se actualiza al guardar cambios
```

---

## 5. Relaciones entre modelos

Este apartado define cómo se conectan las tablas entre sí.

### 5.1. ForeignKey (1:N) — Uno a muchos
*Ejemplo: Un Curso pertenece a una Categoría. Una Categoría tiene muchos Cursos.*

```python
categoria = models.ForeignKey(Categoria, on_delete=models.SET_NULL, null=True)
```

### 5.2. ManyToMany (N:M) — Muchos a muchos (Simple)
*Ejemplo: Un Curso tiene muchas Etiquetas. Una Etiqueta está en muchos Cursos.*
Django crea automáticamente una tabla intermedia invisible.

```python
etiquetas = models.ManyToManyField(Etiqueta, related_name="cursos")
```
*   `related_name`: Permite acceder de manera inversa.
    *   Desde curso: `curso.etiquetas.all()`
    *   Desde etiqueta (inversa): `etiqueta.cursos.all()` (gracias al `related_name`).

### 5.3. ManyToMany con atributos extra (`through`)
*Ejemplo: Un Estudiante se inscribe en un Curso, pero necesitamos guardar la **nota** y la **fecha de inscripción**.*

Una relación N:M simple no basta porque no tiene dónde guardar la nota. Para esto usamos un **modelo intermedio** explícito.

```python
# 1. Definimos la relación en uno de los modelos principales usando 'through'
class Curso(models.Model):
    # ... otros campos
    estudiantes = models.ManyToManyField('Estudiante', through='Inscripcion')

# 2. Definimos el modelo intermedio (Tabla transaccional)
class Inscripcion(models.Model):
    curso = models.ForeignKey(Curso, on_delete=models.CASCADE)
    estudiante = models.ForeignKey('Estudiante', on_delete=models.CASCADE)
    
    # Campos extra
    fecha_inscripcion = models.DateField(auto_now_add=True)
    nota = models.IntegerField()
```

### 5.4. OneToOne (1:1) — Uno a uno
Se usa para **extender un modelo**, habitualmente el usuario. En Django hay un modelo User que ya viene creado por defecto.
*Ejemplo: Un Usuario del sistema tiene un Perfil de Estudiante.*

```python
user = models.OneToOneField(User, on_delete=models.CASCADE)
```

### 5.5. La importancia de `on_delete`
Define qué pasa con el registro "hijo" si borro al "padre":

| Opción | Comportamiento | Ejemplo |
| :--- | :--- | :--- |
| `CASCADE` | Borra al hijo. | Si borro al Usuario, se borra su Perfil. |
| `SET_NULL` | El hijo se queda huérfano (NULL). | Si borro la Categoría, el Curso se queda sin categoría (requiere `null=True`). |
| `PROTECT` | Impide borrar al padre. | No puedo borrar un Profesor si tiene cursos asignados. |

---

## 6. Configuración avanzada: Clase `Meta`

La clase `Meta` dentro de un modelo sirve para configurar metadatos (comportamiento), no datos.

### Opciones visuales y de orden
```python
class Meta:
    ordering = ['-fecha_inicio']    # Orden por defecto (el menos indica descendente)
    verbose_name = "Curso"          # Nombre singular en el admin
    verbose_name_plural = "Cursos"  # Nombre plural en el admin
```

### Restricciones (Constraints)
Para asegurar la integridad de datos a nivel de base de datos. Esto es fundamental para el ejercicio final.

**Ejemplo: Un estudiante no puede inscribirse dos veces al mismo curso.**

```python
from django.db.models import UniqueConstraint

class Inscripcion(models.Model):
    # ... campos ...
    class Meta:
        constraints = [
            models.UniqueConstraint(fields=['estudiante', 'curso'], name='unique_matricula')
        ]
```
*(Nota: Antiguamente se usaba `unique_together`, pero `UniqueConstraint` es la forma moderna).*

---

## 7. Gestión de migraciones

Una vez escrito el código en `models.py`:

1.  **Crear la migración (el plano):**
    ```bash
    python manage.py makemigrations
    ```
    *Esto crea un archivo en la carpeta `migrations/`.*

2.  **Ver qué SQL se va a ejecutar (Opcional):**
    ```bash
    python manage.py sqlmigrate cursos 0001
    ```

3.  **Ejecutar la migración (construir la BD):**
    ```bash
    python manage.py migrate
    ```

4.  **Crear un superusuario para entrar al sistema:**
    ```bash
    python manage.py createsuperuser
    ```

---

## 8. Personalización del Admin (`admin.py`)

En `admin.py` controlamos cómo se ve el panel de control.

*   `list_display`: Columnas que se ven en la tabla.
*   `search_fields`: Barra de búsqueda.
*   `list_filter`: Filtros laterales.
*   `filter_horizontal`: Widget especial para elegir muchos elementos (relaciones N:M).

---

## 9. El ORM en acción (Python Shell)

Para probar sin interfaz gráfica, usamos `python manage.py shell`:

```python
# Crear
c = Categoria.objects.create(nombre="Desarrollo Web")

# Consultar (SELECT)
todos = Curso.objects.all()
python_courses = Curso.objects.filter(titulo__contains="Python")
activos = Curso.objects.filter(activo=True, precio__lt=20.00)

# Relaciones inversas (La magia de Django)
cat = Categoria.objects.get(id=1)
sus_cursos = cat.curso_set.all() # Acceso inverso automático

# Actualizar
c = Curso.objects.get(id=1)
c.precio = 15.50
c.save()
```

---

## 10. Código de ejemplo completo

Estructura de modelos para una gestión de cursos. 
### `models.py`

```python
from django.db import models
from django.contrib.auth.models import User # Importamos el modelo de usuario por defecto

class Categoria(models.Model):
    nombre = models.CharField(max_length=100, unique=True)
    codigo = models.CharField(max_length=10, unique=True, help_text="Ej: DEV, MKT")

    class Meta:
        verbose_name_plural = "Categorías"

    def __str__(self):
        return self.nombre

class Etiqueta(models.Model):
    nombre = models.CharField(max_length=50)

    def __str__(self):
        return self.nombre

class Instructor(models.Model):
    nombre = models.CharField(max_length=100)
    bio = models.TextField(verbose_name="Biografía")
    email = models.EmailField()
    
    def __str__(self):
        return self.nombre

class Estudiante(models.Model):
    # Extendemos el modelo User de Django (Relación 1:1)
    usuario = models.OneToOneField(User, on_delete=models.CASCADE)
    avatar = models.CharField(max_length=255, blank=True, help_text="URL de la imagen")
    fecha_nacimiento = models.DateField(null=True, blank=True)

    def __str__(self):
        return self.usuario.username

class Curso(models.Model):
    class Nivel(models.TextChoices):
        BASICO = 'BAS', 'Nivel Básico'
        INTERMEDIO = 'MED', 'Nivel Intermedio'
        AVANZADO = 'AVZ', 'Nivel Avanzado'

    titulo = models.CharField(max_length=200)
    descripcion = models.TextField()
    
    # Relación 1:N
    categoria = models.ForeignKey(
        Categoria, 
        on_delete=models.SET_NULL, 
        null=True, 
        related_name="cursos" 
    )
    # Relación 1:N
    instructor = models.ForeignKey(Instructor, on_delete=models.CASCADE)
    
    # Relación N:M Simple
    etiquetas = models.ManyToManyField(Etiqueta, blank=True)
    
    # Relación N:M con atributos extra (usando 'through')
    # Esto permite acceder a curso.estudiantes.all()
    estudiantes = models.ManyToManyField(Estudiante, through='Inscripcion')

    # Datos numéricos y fechas
    precio = models.DecimalField(max_digits=6, decimal_places=2, default=0.00)
    fecha_inicio = models.DateField()
    nivel = models.CharField(max_length=1, choices=Nivel.choices, default=Nivel.BASICO)
    activo = models.BooleanField(default=True)

    # Auditoría
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    def __str__(self):
        return f"{self.titulo} ({self.get_nivel_display()})"

class Inscripcion(models.Model):
    """Tabla intermedia explícita (Through Model)"""
    estudiante = models.ForeignKey(Estudiante, on_delete=models.CASCADE)
    curso = models.ForeignKey(Curso, on_delete=models.CASCADE)
    
    # Campos extra de la relación
    fecha_inscripcion = models.DateField(auto_now_add=True)
    nota_final = models.IntegerField(null=True, blank=True, help_text="Nota del 0 al 10")

    class Meta:
        # Evita que un estudiante se matricule 2 veces en el mismo curso
        constraints = [
            models.UniqueConstraint(fields=['estudiante', 'curso'], name='unique_inscripcion')
        ]

    def __str__(self):
        return f"{self.estudiante} en {self.curso}"
```

### `admin.py`

```python
from django.contrib import admin
from .models import Categoria, Etiqueta, Instructor, Curso, Estudiante, Inscripcion

admin.site.register(Categoria)
admin.site.register(Etiqueta)
admin.site.register(Instructor)
admin.site.register(Estudiante)

@admin.register(Curso)
class CursoAdmin(admin.ModelAdmin):
    list_display = ('titulo', 'categoria', 'precio', 'nivel', 'activo')
    list_filter = ('nivel', 'activo', 'categoria')
    search_fields = ('titulo', 'descripcion')
    filter_horizontal = ('etiquetas',) # Widget especial para M2M

@admin.register(Inscripcion)
class InscripcionAdmin(admin.ModelAdmin):
    list_display = ('estudiante', 'curso', 'fecha_inscripcion', 'nota_final')
    list_editable = ('nota_final',) # Permite editar notas directamente en la lista
```

---

## 11. Ejercicio práctico: Proyecto 0

### 11.1 Contexto y temática
Vais a diseñar la **base de datos** y la **API REST** de una aplicación web. En esta fase **NO se programa nada** (o muy poco), solo se diseña.

**Elegid una temática:**
*   Plataforma de *streaming* (películas, usuarios, reseñas).
*   Gestión de liga deportiva (equipos, jugadores, partidos).
*   Biblioteca (libros, autores, préstamos).
*   Tienda *online* (productos, pedidos, clientes).
*   Cualquier otra de tu interés.

### 11.2 Requisitos del modelo de datos
El modelo debe tener **mínimo 5 tablas** y cumplir con lo siguiente:

1.  **Relaciones obligatorias:**
    *   Al menos una **1:1** (Ej: Usuario ↔ Perfil).
    *   Al menos una **1:N** (Ej: Autor → Libros).
    *   Al menos una **N:M con modelo intermedio (`through`)** (Ej: Jugador ↔ Partido, guardando goles/minutos; o Pedido ↔ Producto, guardando cantidad).

2.  **Campos obligatorios:**
    *   Usar variedad de tipos: `CharField`, `IntegerField`, `DecimalField`, `DateField/TimeField`, `BooleanField`.
    *   Incluir campos de auditoría (`created_at`, `updated_at`) en al menos un modelo.

3.  **Configuración Meta:**
    *   Usar `ordering` y `verbose_name`.
    *   Implementar una restricción de unicidad (`UniqueConstraint`) en el modelo intermedio (ej: no repetir el mismo producto en el mismo pedido o el mismo jugador en el mismo partido dos veces).

### 11.3 Diseño de la API (Teórico)
Debéis definir en papel/documento qué *endpoints* tendrá vuestra API.

1.  Elegid **3 recursos principales** (ej: `/peliculas/`, `/actores/`, `/resenas/`).
2.  Para cada uno, definid las operaciones CRUD:
    *   `GET /recurso/` (Listar)
    *   `GET /recurso/{id}/` (Detalle)
    *   `POST /recurso/` (Crear)
    *   `PUT/PATCH /recurso/{id}/` (Editar)
    *   `DELETE /recurso/{id}/` (Borrar)

### 11.4 Entregables

PDF que incluya:

1.  **Diagrama ER** (Entidad-Relación) dibujado.
2.  **Código de `models.py`** (Borrador claro con las clases y campos).
3.  **Tabla de Endpoints** describiendo ruta, método HTTP y qué hace cada uno.
