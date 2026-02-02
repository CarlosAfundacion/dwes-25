import django_filters
from .models import Curso

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