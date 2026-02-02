from django.contrib.auth.models import User
from rest_framework import serializers

from .models import (
    Categoria,
    Etiqueta,
    Instructor,
    Estudiante,
    Curso,
    Inscripcion,
)


class CategoriaSerializer(serializers.ModelSerializer):
    class Meta:
        model = Categoria
        fields = ["id", "nombre", "codigo"]


class EtiquetaSerializer(serializers.ModelSerializer):
    class Meta:
        model = Etiqueta
        fields = ["id", "nombre"]


class InstructorSerializer(serializers.ModelSerializer):
    class Meta:
        model = Instructor
        fields = ["id", "nombre", "bio", "email"]


class UserMiniSerializer(serializers.ModelSerializer):
    class Meta:
        model = User
        fields = ["id", "username", "first_name", "last_name", "email"]



class EstudianteSerializer(serializers.ModelSerializer):
    # Escritura: ID de usuario
    usuario = serializers.PrimaryKeyRelatedField(queryset=User.objects.all())
    # Lectura: detalle
    usuario_detalle = UserMiniSerializer(source="usuario", read_only=True)

    class Meta:
        model = Estudiante
        fields = ["id", "usuario", "usuario_detalle", "avatar", "fecha_nacimiento"]



class InscripcionSerializer(serializers.ModelSerializer):

    estudiante = serializers.PrimaryKeyRelatedField(queryset=Estudiante.objects.all())
    curso = serializers.PrimaryKeyRelatedField(queryset=Curso.objects.all())


    estudiante_detalle = EstudianteSerializer(source="estudiante", read_only=True)
    curso_detalle = serializers.SerializerMethodField(read_only=True)

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



class CursoSerializer(serializers.ModelSerializer):

    class Meta:
        model = Curso
        fields = [
            "id",
            "titulo",
            "descripcion",
            "categoria",
            "instructor",
            "precio",
            "fecha_inicio",
            "nivel",
            "activo",
            "etiquetas",
            "created_at",
            "updated_at",
        ]
        read_only_fields = ["id", "created_at", "updated_at"]

    def validate_precio(self, value):
        if value < 0:
            raise serializers.ValidationError("El precio no puede ser negativo")
        return value

class CursoDetalleSerializer(serializers.ModelSerializer):


    categoria = serializers.PrimaryKeyRelatedField(
        queryset=Categoria.objects.all(),
        allow_null=True,
        required=False
    )
    categoria_detalle = CategoriaSerializer(source="categoria", read_only=True)

    instructor = serializers.PrimaryKeyRelatedField(queryset=Instructor.objects.all())
    instructor_detalle = InstructorSerializer(source="instructor", read_only=True)

    etiquetas = serializers.PrimaryKeyRelatedField(
        many=True,
        queryset=Etiqueta.objects.all(),
        required=False
    )
    etiquetas_detalle = EtiquetaSerializer(source="etiquetas", many=True, read_only=True)

    inscripciones = InscripcionSerializer(many=True, read_only=True)


    class Meta:
        model = Curso
        fields = [
            "id",
            "titulo",
            "descripcion",
            "categoria", "categoria_detalle",
            "instructor", "instructor_detalle",
            "precio",
            "fecha_inicio",
            "nivel",
            "activo",
            "etiquetas", "etiquetas_detalle",
            "inscripciones",
            "created_at",
            "updated_at",
        ]
        read_only_fields = ["id", "created_at", "updated_at"]
