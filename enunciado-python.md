# 0) Pasos previos

Crea una carpeta llamada sprint0python en tu repositorio de Desarrollo de Interfaces. 

Haz un commit después de cada ejercicio terminado. 

Valida todas las entradas del usuario.

Usa excepciones siempre que sea necesario.

# 🧩 1) Adivina el número (con niveles)

## 🎯 Objetivo

Practicar bucles, condicionales, validación de entrada y manejo de números aleatorios.

---

## 📘 Descripción

El programa elige un número secreto al azar y el usuario debe adivinarlo.
En cada intento, el programa dice si el número es **más alto** o **más bajo**.

---

## 🧱 Pasos de implementación

### 1️⃣ Presentación del juego

Muestra un mensaje explicando las reglas:

> "El programa pensará un número entre 1 y un máximo que tú elijas.
> Intenta adivinarlo con la menor cantidad de intentos posible."

### 2️⃣ Elegir nivel de dificultad

* Pide al usuario un nivel: “fácil”, “medio” o “difícil”.
* Según la elección:

  * fácil → entre 1 y 50
  * medio → entre 1 y 100
  * difícil → entre 1 y 500
* Si introduce algo distinto, vuelve a pedirlo.

**Pista:** puedes usar un bucle `while True` con una condición de salida cuando la entrada sea válida.

---

### 3️⃣ Generar el número secreto

* Importa el módulo `random` (al inicio del archivo).
* Usa su función para generar un número entero dentro del rango elegido.
  Por ejemplo: `numero = random.randint(1, 100)`

---

### 4️⃣ Bucle principal de intentos

* Pide al usuario un número.
* Si no introduce un entero válido, muestra un mensaje de error y vuelve a pedirlo (usa `try/except ValueError`).
* Compara el número con el secreto:

  * Si es menor → “Demasiado bajo.”
  * Si es mayor → “Demasiado alto.”
  * Si acierta → “¡Felicidades! Adivinaste en X intentos.”

Cuenta cada intento con una variable.

---

### 5️⃣ Volver a jugar

* Al final, pregunta: “¿Quieres jugar otra vez? (s/n)”.
* Si responde “s”, vuelve a empezar desde el nivel.
* Si no, muestra un mensaje de despedida y termina.

---

## 🧠 Teoría útil

* `random.randint(a, b)` genera un número aleatorio entre a y b, ambos incluidos.
* Un **bucle `while`** sirve para repetir instrucciones hasta que se cumpla una condición.
* Las **excepciones** (`try/except`) permiten controlar errores sin que el programa se cierre.

---

# 🖐️ 2) Piedra, Papel, Tijera, Lagarto, Spock

## 🎯 Objetivo

Practicar listas, diccionarios, funciones, control de flujo y validación de entrada.
También introducir la idea de **estructuras de reglas** para modelar comportamientos.

---

## 📘 Descripción

Versión ampliada del clásico juego, donde el usuario juega contra la máquina.

---

## 🧱 Pasos de implementación

### 1️⃣ Reglas

Define las reglas del juego:

* Tijera corta papel y decapita lagarto.
* Papel cubre piedra y refuta Spock.
* Piedra aplasta tijera y aplasta lagarto.
* Lagarto envenena Spock y devora papel.
* Spock vaporiza piedra y rompe tijera.

---

### 2️⃣ Lista de opciones

Crea una lista con todas las opciones:

```
["piedra", "papel", "tijera", "lagarto", "spock"]
```

---

### 3️⃣ Estructura de reglas

Crea un diccionario donde cada jugada “gana a” dos elementos.
Ejemplo orientativo:

```
{
  "tijera": ["papel", "lagarto"],
  "papel": ["piedra", "spock"],
  ...
}
```

---

### 4️⃣ Función para determinar el resultado

Crea una función que reciba dos jugadas (usuario y CPU) y devuelva:

* 0 si empate
* 1 si gana el usuario
* -1 si gana la CPU

Dentro, usa `if/elif` o consulta el diccionario.

---

### 5️⃣ Jugar una ronda

* El usuario escribe su jugada.
* La CPU elige una jugada al azar.
* Muestra ambas y el resultado (“gana usuario”, “gana CPU”, “empate”).

Valida la entrada: si el texto no está en la lista de opciones, vuelve a pedirlo.

---

### 6️⃣ Mejor de N

* Pide un número **impar** mayor o igual que 1 (N).
* Quien gane `N//2 + 1` rondas, gana la partida.
* Muestra el marcador después de cada ronda.
* Al final, anuncia el ganador general.

---

### 7️⃣ Repetir partida

Pregunta al final: “¿Quieres jugar otra vez? (s/n)” y actúa en consecuencia.

---

## 🧠 Teoría útil

* Un **diccionario** permite asociar claves (jugadas) con listas (a quién gana).
* Los **bucles `while`** y las estructuras condicionales (`if/elif/else`) son esenciales para repetir y controlar decisiones.
* Las funciones pueden **devolver valores** usando `return`.

---

# 💰 3) Simulador de Cajero Automático

## 🎯 Objetivo

Trabajar diccionarios, validación de datos numéricos y bucles con menús.

---

## 📘 Descripción

El programa simula las operaciones básicas de un cajero automático para un solo usuario.

---

## 🧱 Pasos de implementación

### 1️⃣ Representar una cuenta

Crea una estructura con:

* `nombre`
* `saldo`

Un **diccionario** es ideal:

```
cuenta = {"nombre": "Ana", "saldo": 1200.0}
```

---

### 2️⃣ Mostrar menú

El menú debe tener:

1. Consultar saldo
2. Ingresar dinero
3. Retirar dinero
4. Salir

Usa un bucle `while` que repita hasta que el usuario elija salir.

---

### 3️⃣ Consultar saldo

Muestra el saldo actual con 2 decimales (puedes usar f-string).

---

### 4️⃣ Ingresar dinero

* Pide una cantidad.
* Convierte a `float` dentro de un bloque `try/except`.
* Si es positiva, **súmala** al saldo.
* Si es negativa o no numérica, muestra un mensaje de error y vuelve a pedir.

---

### 5️⃣ Retirar dinero

* Pide cantidad.
* Si es mayor que el saldo, muestra “Saldo insuficiente”.
* Si es válida, **réstala** al saldo.

---

### 6️⃣ Salir

* Mensaje de despedida y fin del bucle.

---

## 🧠 Teoría útil

* Los diccionarios permiten agrupar información con nombre y valor.
* `try/except` evita que el programa se bloquee si el usuario escribe mal.
* Los bucles `while True` son útiles para menús interactivos.

---


# 🛒 4) Gestor de lista de la compra

## 🎯 Objetivo

Practicar listas, bucles, búsqueda, ordenación y funciones.

---

## 📘 Descripción

Aplicación por consola para gestionar una lista de la compra.

---

## 🧱 Pasos de implementación

### 1️⃣ Crear lista vacía

La lista contendrá los nombres de los productos:

```
lista_compra = []
```

---

### 2️⃣ Menú

Opciones:

1. Añadir producto
2. Eliminar producto
3. Ver lista
4. Vaciar lista
5. Salir

---

### 3️⃣ Añadir producto

* Pide un nombre de producto (texto).
* Convierte a minúsculas y elimina espacios.
* Si ya está en la lista, avisa.
* Si no, añádelo con `append`.

---

### 4️⃣ Eliminar producto

* Pide un nombre.
* Si está en la lista, elimínalo con `remove`.
* Si no está, muestra mensaje de error.

---

### 5️⃣ Ver lista

* Muestra todos los productos ordenados alfabéticamente.
* Si la lista está vacía, muestra un mensaje.

---

### 6️⃣ Vaciar lista

* Pide confirmación (s/n).
* Si sí, usa `clear()`.

---

### 7️⃣ Salir

Finaliza el programa.

---

## 🧠 Teoría útil

* `in` sirve para comprobar si un elemento está en una lista.
* `sorted(lista)` devuelve una **copia ordenada** sin modificar la original.
* `list.remove()` elimina el primer elemento que coincide.


---

# ✅ 5) Gestor de tareas (POO básica, pre-Django)

## 🎯 Objetivo

Introducir clases, objetos, atributos, métodos y colecciones de instancias.
Este ejercicio prepara para entender los modelos de Django.

---

## 📘 Descripción

Aplicación por consola que permite crear, listar, editar, completar y eliminar tareas.

---

## 🧱 Pasos de implementación

### 1️⃣ Mini teoría

**Clases y objetos:**

* Una **clase** es un “molde” o “plantilla”.
* Un **objeto** (o **instancia**) es una cosa concreta creada a partir de esa clase.
* El método `__init__` se ejecuta automáticamente al crear el objeto y sirve para inicializar sus datos.

---

### 2️⃣ Clase `Tarea`

Define una clase con tres atributos:

* `titulo`
* `descripcion`
* `completada` (inicialmente `False`)

Y tres métodos:

* `mostrar_info()` → devuelve una cadena con título y estado (pendiente/completada).
* `marcar_completada()` → cambia `completada` a `True`.
* `editar(nuevo_titulo, nueva_descripcion)` → actualiza los atributos.

---

### 3️⃣ Lista de tareas

En el `main`, crea una lista vacía `tareas = []`.
Cada vez que el usuario crea una tarea, **añádela** a la lista.

---

### 4️⃣ Menú principal

1. Crear tarea
2. Mostrar todas
3. Marcar como completada
4. Editar tarea
5. Eliminar tarea
6. Salir

---

### 5️⃣ Funcionalidades

* **Crear**: pide título y descripción.
* **Mostrar**: recorre la lista y muestra `mostrar_info()`.
* **Marcar completada**: busca por título (sin distinguir mayúsculas).
* **Editar**: pide nuevos valores.
* **Eliminar**: borra si existe.
* **Salir**: termina el programa.

---

### 6️⃣ Buenas prácticas

* Usa nombres `CamelCase` para la clase y `snake_case` para variables.


---


## 🧠 Teoría útil

* `self` representa la propia instancia del objeto.
* Cada objeto tiene sus propios valores de atributos.
* Los métodos se llaman con la notación `objeto.metodo()`.

---


