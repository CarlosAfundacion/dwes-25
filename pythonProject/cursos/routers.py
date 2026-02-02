# cursos/routers.py
from rest_framework.routers import DefaultRouter
from .views import (
    CategoriaViewSet, EtiquetaViewSet, InstructorViewSet,
    EstudianteViewSet, CursoViewSet, InscripcionViewSet
)

router = DefaultRouter()
router.register(r"categorias", CategoriaViewSet, basename="categoria")
router.register(r"etiquetas", EtiquetaViewSet, basename="etiqueta")
router.register(r"instructores", InstructorViewSet, basename="instructor")
router.register(r"estudiantes", EstudianteViewSet, basename="estudiante")
router.register(r"cursos", CursoViewSet, basename="curso")
router.register(r"inscripciones", InscripcionViewSet, basename="inscripcion")
