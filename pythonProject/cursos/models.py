from django.db import models
from django.contrib.auth.models import User


class Categoria(models.Model):
    nombre = models.CharField(max_length=100, unique=True)
    codigo = models.CharField(max_length=10, unique=True, help_text="Ej: DEV, MKT")

    class Meta:
        ordering = ["nombre"]
        verbose_name = "Categoría"
        verbose_name_plural = "Categorías"

    def __str__(self):
        return self.nombre


class Etiqueta(models.Model):
    nombre = models.CharField(max_length=50)

    class Meta:
        ordering = ["nombre"]
        verbose_name = "Etiqueta"
        verbose_name_plural = "Etiquetas"

    def __str__(self):
        return self.nombre


class Instructor(models.Model):
    nombre = models.CharField(max_length=100)
    bio = models.TextField(verbose_name="Biografía")
    email = models.EmailField()

    class Meta:
        ordering = ["nombre"]
        verbose_name = "Instructor"
        verbose_name_plural = "Instructores"

    def __str__(self):
        return self.nombre


class Estudiante(models.Model):
    usuario = models.OneToOneField(User, on_delete=models.CASCADE)
    avatar = models.CharField(max_length=255, blank=True, help_text="URL de la imagen")
    fecha_nacimiento = models.DateField(null=True, blank=True)

    class Meta:
        ordering = ["usuario__username"]
        verbose_name = "Estudiante"
        verbose_name_plural = "Estudiantes"

    def __str__(self):
        return self.usuario.username


class Curso(models.Model):
    class Nivel(models.TextChoices):
        BASICO = "1", "Nivel Básico"
        INTERMEDIO = "2", "Nivel Intermedio"
        AVANZADO = "3", "Nivel Avanzado"

    instructor = models.ForeignKey(
        Instructor,
        on_delete=models.CASCADE,
        related_name="cursos"
    )

    titulo = models.CharField(max_length=200)
    descripcion = models.TextField()

    categoria = models.ForeignKey(
        Categoria,
        on_delete=models.SET_NULL,
        null=True,
        related_name="cursos"
    )

    etiquetas = models.ManyToManyField(Etiqueta, blank=True)

    estudiantes = models.ManyToManyField(Estudiante, through="Inscripcion")

    precio = models.DecimalField(max_digits=6, decimal_places=2, default=0.00)
    fecha_inicio = models.DateField()
    nivel = models.CharField(max_length=1, choices=Nivel.choices, default=Nivel.BASICO)
    activo = models.BooleanField(default=True)

    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        ordering = ["-fecha_inicio"]
        verbose_name = "Curso"
        verbose_name_plural = "Cursos"

    def __str__(self):
        return f"{self.titulo} ({self.get_nivel_display()})"


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
    nota_final = models.IntegerField(null=True, blank=True, help_text="Nota del 0 al 10")

    class Meta:
        ordering = ["-fecha_inscripcion"]
        verbose_name = "Inscripción"
        verbose_name_plural = "Inscripciones"
        constraints = [
            models.UniqueConstraint(
                fields=["estudiante", "curso"],
                name="unique_inscripcion"
            )
        ]

    def __str__(self):
        return f"{self.estudiante} en {self.curso}"
