from django.contrib import admin
from .models import Categoria, Etiqueta, Instructor, Curso, Estudiante, Inscripcion

admin.site.register(Categoria)
admin.site.register(Etiqueta)
admin.site.register(Instructor)


@admin.register(Curso)
class CursoAdmin(admin.ModelAdmin):
    list_display = ('pk','titulo', 'categoria', 'precio', 'nivel', 'activo')
    list_filter = ('nivel', 'activo', 'categoria')
    search_fields = ('titulo', 'descripcion')
    filter_horizontal = ('etiquetas',) # Widget especial para M2M

@admin.register(Inscripcion)
class InscripcionAdmin(admin.ModelAdmin):
    list_display = ('estudiante', 'curso', 'fecha_inscripcion', 'nota_final')
    list_editable = ('nota_final',) # Permite editar notas directamente en la lista
@admin.register(Estudiante)
class Estudiantes(admin.ModelAdmin):
    list_display = ('pk', 'username','email', 'is_active')
    @admin.display(description='Usuario')
    def username(self, obj):
        return obj.usuario.username

    @admin.display(description='Email')
    def email(self, obj):
        return obj.usuario.email

    @admin.display(description='Activo')
    def is_active(self, obj):
        return obj.usuario.is_active