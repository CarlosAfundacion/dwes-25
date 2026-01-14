## 12. De una pantalla a una aplicación: el problema de la navegación

Hasta ahora, *Aula+ Lite* es una aplicación de **una sola pantalla**. Esto era intencionado:

* Nos permitió centrarnos en:

  * MVVM correcto
  * Estado en ViewModel
  * UX/UI en una pantalla real
  * Material Design en inputs/botones/feedback

Pero en cuanto una app tiene:

* Login
* Registro
* Home
* Detalle
* Ajustes

aparece un problema nuevo:

> **¿Quién decide a dónde se navega y cuándo?**

Y, sobre todo:

> **¿Cómo garantizamos que el flujo sea coherente y mantenible?**

---

## 13. Navegación clásica con Activities: por qué no escala

El enfoque tradicional es lanzar Activities con `Intent`:

```java
Intent intent = new Intent(this, HomeActivity.class);
startActivity(intent);
```

Funciona, pero a medio plazo falla por diseño:

* La navegación queda **dispersa** (cada botón navega “a su manera”)
* No hay “mapa” del flujo
* El botón atrás se vuelve impredecible si el flujo crece
* Es difícil aplicar reglas de negocio de navegación:

  * “Si no estás logueado, no entras a Home”
  * “Después de login no se vuelve atrás”
* La vista termina decidiendo cosas que no le corresponden

En DI esto es importante:

> Una app profesional no solo “navega”, sino que navega con **flujo controlado**.

---

## 14. Enfoque profesional: navegación declarativa

Android propone un enfoque distinto:

* En lugar de programar navegación botón a botón,
* se define un **grafo de navegación**:

1. Qué pantallas existen
2. Cómo se conectan
3. Cuál es la pantalla inicial
4. Cómo se gestiona el “atrás”

Esto da:

* **Mantenibilidad**
* **Visión global**
* **Flujo controlable**
* **Menos bugs de backstack**

---

## 15. Cambio conceptual importante (sin romper lo anterior)

A partir de ahora:

* La **Activity deja de ser la pantalla**
* La Activity pasa a ser un **contenedor**
* Cada pantalla visible será un **Fragment**

Esto **no rompe MVVM**:

| Antes                     | Ahora                     |
| ------------------------- | ------------------------- |
| Activity = Vista          | Fragment = Vista          |
| ViewModel = lógica/estado | ViewModel = lógica/estado |
| Repository = datos        | Repository = datos        |

Lo que cambia es **quién pinta** la interfaz, no el patrón.

---

## 16. La Activity como contenedor de navegación

La `MainActivity` deja de tener inputs, botones o lógica de UI. Su única responsabilidad será:

* Contener un `NavHost` (un “marco” donde se cargan fragments)
* Delegar la navegación al sistema

### 16.1 `activity_main.xml` (solo NavHost)

Cambia tu `res/layout/activity_main.xml` para que sea solo el host:

```xml
<?xml version="1.0" encoding="utf-8"?>
<androidx.fragment.app.FragmentContainerView
    xmlns:android="http://schemas.android.com/apk/res/android"
    xmlns:app="http://schemas.android.com/apk/res-auto"
    android:id="@+id/nav_host"
    android:name="androidx.navigation.fragment.NavHostFragment"
    android:layout_width="match_parent"
    android:layout_height="match_parent"
    app:navGraph="@navigation/nav_graph"
    app:defaultNavHost="true" />
```

**Qué significa esto:**

* La Activity ya no “decide” qué pantalla se ve.
* El grafo (`nav_graph`) decide qué fragment aparece.
* `defaultNavHost="true"` hace que el botón atrás lo gestione Navigation.

### 16.2 `MainActivity.java` (mínima)

```java
package com.example.aula.ui;

import android.os.Bundle;
import androidx.appcompat.app.AppCompatActivity;
import com.example.aula.R;

public class MainActivity extends AppCompatActivity {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
    }
}
```

---

## 17. El grafo de navegación: mapa de la aplicación

<img width="2006" height="1090" alt="imagen" src="https://github.com/user-attachments/assets/02a854cf-7722-4580-8c4c-cdafcd315517" />

El archivo `res/navigation/nav_graph.xml` define:

* Pantallas (destinos)
* Conexiones (acciones)
* Pantalla inicial (startDestination)

### 17.1 `nav_graph.xml` (Login → Register → Home)

```xml
<?xml version="1.0" encoding="utf-8"?>
<navigation xmlns:android="http://schemas.android.com/apk/res/android"
    xmlns:app="http://schemas.android.com/apk/res-auto"
    android:id="@+id/nav_graph"
    app:startDestination="@id/loginFragment">

    <fragment
        android:id="@+id/loginFragment"
        android:name="com.example.aula.ui.fragments.LoginFragment"
        android:label="Login">

        <action
            android:id="@+id/action_login_to_register"
            app:destination="@id/registerFragment"/>

        <action
            android:id="@+id/action_login_to_home"
            app:destination="@id/homeFragment"/>
    </fragment>

    <fragment
        android:id="@+id/registerFragment"
        android:name="com.example.aula.ui.fragments.RegisterFragment"
        android:label="Registro">

        <action
            android:id="@+id/action_register_to_login"
            app:destination="@id/loginFragment"/>
    </fragment>

    <fragment
        android:id="@+id/homeFragment"
        android:name="com.example.aula.ui.fragments.HomeFragment"
        android:label="Home"/>
</navigation>
```

**Idea DI clave:**

> El flujo se puede revisar sin leer Java: está “dibujado” en el grafo.

---

## 18. Primera fragmentación de Aula+ Lite

Dividimos la app en tres pantallas:

* `LoginFragment` → acceso
* `RegisterFragment` → alta
* `HomeFragment` → **la pantalla original de Aula+ Lite**, migrada a fragment

**Importante:**

* No “tiramos” MVVM.
* El ViewModel de avisos se mantiene.
* La lógica y repositorio de avisos siguen siendo válidos.

---

## 19. Navegar desde un Fragment (sin Intents)

En un fragment, navegar se hace así:

```java
NavHostFragment.findNavController(this)
        .navigate(R.id.action_login_to_register);
```

Ventajas:

* Navegación coherente
* Backstack gestionado
* Menos código repetido
* Flujo centralizado en el grafo

---

##  PRÁCTICA 4 — NAVEGACIÓN Y ESTRUCTURA (colocada donde toca)

**Incremental sobre Aula+ Lite (obligatoria)**

En este punto ya tienes: Activity contenedor, NavHost, grafo y navegación.

### Objetivo
<img width="600" height="519" alt="imagen" src="https://github.com/user-attachments/assets/ec61aafd-f02e-41f1-a0f5-fe8473034e3f" />

Convertir Aula+ Lite en una app con **flujo real de pantallas** sin usar Intents.

### Tareas

1. Convertir la app en **una única Activity** contenedora (`MainActivity`).
2. Crear:

   * `LoginFragment`
   * `RegisterFragment`
   * `HomeFragment`
3. Crear el grafo `nav_graph.xml` con:

   * Login → Register
   * Login → Home (fake de momento)
   * Register → Login
4. Migrar el contenido de tu `activity_main.xml` original (Aula+ Lite) a un layout de `HomeFragment`.
5. Prohibido:

   * `Intent`
   * múltiples Activities
6. Comprobación obligatoria:

   * El botón atrás vuelve al sitio correcto **sin programarlo manualmente**.

### Código de ejemplo mínimo (LoginFragment “fake” para validar navegación)

`ui/fragments/LoginFragment.java` (versión A3 “sin Firebase aún”):

```java
package com.example.aula.ui.fragments;

import android.os.Bundle;
import android.view.View;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.navigation.fragment.NavHostFragment;

import com.example.aula.R;
import com.google.android.material.button.MaterialButton;

public class LoginFragment extends Fragment {

    public LoginFragment() {
        super(R.layout.fragment_login);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {

        MaterialButton btnLogin = view.findViewById(R.id.btnLogin);
        MaterialButton btnGoRegister = view.findViewById(R.id.btnGoRegister);

        btnGoRegister.setOnClickListener(v ->
                NavHostFragment.findNavController(this)
                        .navigate(R.id.action_login_to_register)
        );

        // A3: login fake (siempre navega)
        btnLogin.setOnClickListener(v ->
                NavHostFragment.findNavController(this)
                        .navigate(R.id.action_login_to_home)
        );
    }
}
```

---

## 20. Estado de sesión: nuevo problema real

Ahora que existe un Home y un Login, aparece un requisito real:

> **Si el usuario ya está autenticado, no debe volver a ver el Login.**

Esto implica dos cosas:

* Autenticación real (A4)
* Persistencia de sesión (Firebase ya la ofrece, pero hay que usarla bien)

---

## 21. Firebase Authentication: identidad del usuario

Firebase Auth gestiona:

* Registro
* Login
* Sesión (mantiene el usuario autenticado)

**Punto crítico:**

* Auth **no almacena datos de la app**.
* Solo gestiona **quién es el usuario**.

Más adelante Firestore usará ese usuario para asociar datos.

---

## 22. Separación clara de responsabilidades

En una app profesional:

* UI no habla directamente con servicios externos sin encapsular
* La UI no decide “qué hacer con errores técnicos”
* La UI solo:

  * recoge inputs
  * observa estado
  * navega

Estructura mínima recomendada (manteniendo tu estilo):

```
data/
  AuthRepository.java
viewmodel/
  UiState.java
  AuthViewModel.java
  AuthViewModelFactory.java
ui/fragments/
  LoginFragment.java
  RegisterFragment.java
  HomeFragment.java
```

---

## 23. Estados de interfaz en autenticación (UX real)

Una pantalla de login no es binaria. Tiene estados:

* Idle (esperando)
  <img width="450" height="378" alt="imagen" src="https://github.com/user-attachments/assets/1476c172-5c72-4512-abf7-19edaed73159" />

* Loading (autenticando)
 <img width="550" height="600" alt="imagen" src="https://github.com/user-attachments/assets/eb5abb6f-b9cf-4973-a845-856d7b988f3b" />

* Error (mensaje)
* Success (navegar)

Y esos estados deben reflejarse en la UI:

* Mostrar `ProgressIndicator`
* Desactivar botones mientras carga
* Mostrar error integrado

---

## 24. Validación previa: UX obligatoria

Antes de enviar al backend:

* Email vacío → error inmediato
* Email mal formado → error inmediato
* Password corta → error inmediato

Eso se muestra en el **campo**, con `TextInputLayout`.

Ejemplo conceptual:

```java
tilEmail.setError("Email no válido");
tilPassword.setError("Mínimo 6 caracteres");
```

Regla DI:

> No se consulta al servidor por errores que el cliente ya puede detectar.

---

## 25. Implementación completa: AuthRepository + ViewModel + State

Para mantener MVVM y tu estilo con Factory, creamos:

* `UiState` (estado UI)
* `AuthRepository` (encapsula FirebaseAuth)
* `AuthViewModel` (orquesta estados)
* `AuthViewModelFactory` (inyección del repo)

### 25.1 `UiState.java`

```java
package com.example.aula.viewmodel;

public class UiState {
    public final boolean loading;
    public final String error;
    public final boolean success;

    public UiState(boolean loading, String error, boolean success) {
        this.loading = loading;
        this.error = error;
        this.success = success;
    }

    public static UiState idle() { return new UiState(false, null, false); }
    public static UiState loading() { return new UiState(true, null, false); }
    public static UiState error(String msg) { return new UiState(false, msg, false); }
    public static UiState success() { return new UiState(false, null, true); }
}
```

### 25.2 `AuthRepository.java` (encapsula FirebaseAuth)

```java
package com.example.aula.data;

import androidx.annotation.NonNull;

import com.google.firebase.auth.FirebaseAuth;

public class AuthRepository {

    private final FirebaseAuth auth = FirebaseAuth.getInstance();

    public interface Callback {
        void onOk();
        void onError(@NonNull String message);
    }

    public boolean isLoggedIn() {
        return auth.getCurrentUser() != null;
    }

    public void login(String email, String password, Callback cb) {
        auth.signInWithEmailAndPassword(email, password)
                .addOnSuccessListener(result -> cb.onOk())
                .addOnFailureListener(e -> cb.onError(
                        e.getMessage() != null ? e.getMessage() : "Error desconocido"
                ));
    }

    public void register(String email, String password, Callback cb) {
        auth.createUserWithEmailAndPassword(email, password)
                .addOnSuccessListener(result -> cb.onOk())
                .addOnFailureListener(e -> cb.onError(
                        e.getMessage() != null ? e.getMessage() : "Error desconocido"
                ));
    }

    public void logout() {
        auth.signOut();
    }
}
```

### 25.3 `AuthViewModel.java` (estado + acciones)

```java
package com.example.aula.viewmodel;

import androidx.lifecycle.LiveData;
import androidx.lifecycle.MutableLiveData;
import androidx.lifecycle.ViewModel;

import com.example.aula.data.AuthRepository;

public class AuthViewModel extends ViewModel {

    private final AuthRepository repo;

    private final MutableLiveData<UiState> _state = new MutableLiveData<>(UiState.idle());
    public LiveData<UiState> getState() { return _state; }

    // Evento one-shot (mismo patrón que tu eventoToast)
    private final MutableLiveData<String> _eventMessage = new MutableLiveData<>(null);
    public LiveData<String> getEventMessage() { return _eventMessage; }
    public void consumeEventMessage() { _eventMessage.setValue(null); }

    public AuthViewModel(AuthRepository repo) {
        this.repo = repo;
    }

    public boolean isLoggedIn() {
        return repo.isLoggedIn();
    }

    public void login(String email, String password) {
        _state.setValue(UiState.loading());
        repo.login(email, password, new AuthRepository.Callback() {
            @Override public void onOk() {
                _state.postValue(UiState.success());
            }
            @Override public void onError(String message) {
                _state.postValue(UiState.error(message));
            }
        });
    }

    public void register(String email, String password) {
        _state.setValue(UiState.loading());
        repo.register(email, password, new AuthRepository.Callback() {
            @Override public void onOk() {
                _state.postValue(UiState.success());
            }
            @Override public void onError(String message) {
                _state.postValue(UiState.error(message));
            }
        });
    }

    public void logout() {
        repo.logout();
        _eventMessage.setValue("Sesión cerrada");
    }
}
```

### 25.4 `AuthViewModelFactory.java` (mismo estilo que NoticeViewModelFactory)

```java
package com.example.aula.viewmodel;

import androidx.annotation.NonNull;
import androidx.lifecycle.ViewModel;
import androidx.lifecycle.ViewModelProvider;

import com.example.aula.data.AuthRepository;

public class AuthViewModelFactory implements ViewModelProvider.Factory {

    private final AuthRepository repo;

    public AuthViewModelFactory(AuthRepository repo) {
        this.repo = repo;
    }

    @NonNull
    @Override
    @SuppressWarnings("unchecked")
    public <T extends ViewModel> T create(@NonNull Class<T> modelClass) {
        if (modelClass.isAssignableFrom(AuthViewModel.class)) {
            return (T) new AuthViewModel(repo);
        }
        throw new IllegalArgumentException("Unknown ViewModel class");
    }
}
```

---

## 26. Layout profesional de Login (Material 3 + Progress + Jerarquía)

Aquí aplicamos lo trabajado en inputs y botones Material.

### 26.1 `fragment_login.xml`

Incluye:

* `TextInputLayout` para email
* `TextInputLayout` con `password_toggle`
* Botón principal
* Botón secundario (outlined)
* `CircularProgressIndicator` para loading

```xml
<?xml version="1.0" encoding="utf-8"?>
<androidx.constraintlayout.widget.ConstraintLayout
    xmlns:android="http://schemas.android.com/apk/res/android"
    xmlns:app="http://schemas.android.com/apk/res-auto"
    android:layout_width="match_parent"
    android:layout_height="match_parent"
    android:padding="20dp">

    <TextView
        android:id="@+id/tvTitle"
        android:layout_width="0dp"
        android:layout_height="wrap_content"
        android:text="Aula+ — Login"
        android:textSize="24sp"
        android:textStyle="bold"
        app:layout_constraintTop_toTopOf="parent"
        app:layout_constraintStart_toStartOf="parent"
        app:layout_constraintEnd_toEndOf="parent"/>

    <com.google.android.material.textfield.TextInputLayout
        android:id="@+id/tilEmail"
        android:layout_width="0dp"
        android:layout_height="wrap_content"
        android:hint="Email"
        android:layout_marginTop="16dp"
        app:layout_constraintTop_toBottomOf="@id/tvTitle"
        app:layout_constraintStart_toStartOf="parent"
        app:layout_constraintEnd_toEndOf="parent">

        <com.google.android.material.textfield.TextInputEditText
            android:id="@+id/etEmail"
            android:layout_width="match_parent"
            android:layout_height="wrap_content"
            android:inputType="textEmailAddress"/>
    </com.google.android.material.textfield.TextInputLayout>

    <com.google.android.material.textfield.TextInputLayout
        android:id="@+id/tilPassword"
        android:layout_width="0dp"
        android:layout_height="wrap_content"
        android:hint="Contraseña"
        android:layout_marginTop="10dp"
        app:endIconMode="password_toggle"
        app:layout_constraintTop_toBottomOf="@id/tilEmail"
        app:layout_constraintStart_toStartOf="parent"
        app:layout_constraintEnd_toEndOf="parent">

        <com.google.android.material.textfield.TextInputEditText
            android:id="@+id/etPassword"
            android:layout_width="match_parent"
            android:layout_height="wrap_content"
            android:inputType="textPassword"/>
    </com.google.android.material.textfield.TextInputLayout>

    <com.google.android.material.button.MaterialButton
        android:id="@+id/btnLogin"
        android:layout_width="0dp"
        android:layout_height="wrap_content"
        android:text="Entrar"
        android:layout_marginTop="14dp"
        app:layout_constraintTop_toBottomOf="@id/tilPassword"
        app:layout_constraintStart_toStartOf="parent"
        app:layout_constraintEnd_toEndOf="parent"/>

    <com.google.android.material.button.MaterialButton
        android:id="@+id/btnGoRegister"
        style="?attr/materialButtonOutlinedStyle"
        android:layout_width="0dp"
        android:layout_height="wrap_content"
        android:text="Crear cuenta"
        android:layout_marginTop="8dp"
        app:layout_constraintTop_toBottomOf="@id/btnLogin"
        app:layout_constraintStart_toStartOf="parent"
        app:layout_constraintEnd_toEndOf="parent"/>

    <com.google.android.material.progressindicator.CircularProgressIndicator
        android:id="@+id/progress"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:visibility="gone"
        android:layout_marginTop="16dp"
        app:layout_constraintTop_toBottomOf="@id/btnGoRegister"
        app:layout_constraintStart_toStartOf="parent"
        app:layout_constraintEnd_toEndOf="parent"/>

</androidx.constraintlayout.widget.ConstraintLayout>
```

---

## 27. Login real con MVVM + validación + loading + Snackbar + backstack limpio

Aquí unimos TODO:

* Validación previa (errores en campos)
* Auth real (Firebase)
* Loading (progress + botones desactivados)
* Snackbar (errores globales)
* Navegación a Home limpiando backstack (no volver a Login)

### 27.1 `LoginFragment.java` (completo)

```java
package com.example.aula.ui.fragments;

import android.os.Bundle;
import android.util.Patterns;
import android.view.View;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.lifecycle.ViewModelProvider;
import androidx.navigation.NavOptions;
import androidx.navigation.fragment.NavHostFragment;

import com.example.aula.R;
import com.example.aula.data.AuthRepository;
import com.example.aula.viewmodel.AuthViewModel;
import com.example.aula.viewmodel.AuthViewModelFactory;
import com.google.android.material.button.MaterialButton;
import com.google.android.material.progressindicator.CircularProgressIndicator;
import com.google.android.material.snackbar.Snackbar;
import com.google.android.material.textfield.TextInputLayout;

public class LoginFragment extends Fragment {

    private AuthViewModel vm;

    public LoginFragment() {
        super(R.layout.fragment_login);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {

        TextInputLayout tilEmail = view.findViewById(R.id.tilEmail);
        TextInputLayout tilPassword = view.findViewById(R.id.tilPassword);

        MaterialButton btnLogin = view.findViewById(R.id.btnLogin);
        MaterialButton btnGoRegister = view.findViewById(R.id.btnGoRegister);
        CircularProgressIndicator progress = view.findViewById(R.id.progress);

        vm = new ViewModelProvider(
                this,
                new AuthViewModelFactory(new AuthRepository())
        ).get(AuthViewModel.class);

        // Si ya está logueado, saltamos a Home directamente
        if (vm.isLoggedIn()) {
            navigateToHomeClearingBackstack();
            return;
        }

        btnGoRegister.setOnClickListener(v ->
                NavHostFragment.findNavController(this)
                        .navigate(R.id.action_login_to_register)
        );

        btnLogin.setOnClickListener(v -> {
            // Limpiar errores previos
            tilEmail.setError(null);
            tilPassword.setError(null);

            String email = tilEmail.getEditText() != null
                    ? tilEmail.getEditText().getText().toString().trim()
                    : "";

            String password = tilPassword.getEditText() != null
                    ? tilPassword.getEditText().getText().toString()
                    : "";

            // Validaciones previas (UX)
            if (!Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
                tilEmail.setError("Email no válido");
                return;
            }
            if (password.length() < 6) {
                tilPassword.setError("Mínimo 6 caracteres");
                return;
            }

            vm.login(email, password);
        });

        vm.getState().observe(getViewLifecycleOwner(), state -> {
            if (state == null) return;

            // Loading
            progress.setVisibility(state.loading ? View.VISIBLE : View.GONE);
            btnLogin.setEnabled(!state.loading);
            btnGoRegister.setEnabled(!state.loading);

            // Error global (Snackbar)
            if (state.error != null) {
                Snackbar.make(view, state.error, Snackbar.LENGTH_LONG).show();
            }

            // Éxito -> navegar
            if (state.success) {
                navigateToHomeClearingBackstack();
            }
        });
    }

    private void navigateToHomeClearingBackstack() {
        NavOptions options = new NavOptions.Builder()
                .setPopUpTo(R.id.loginFragment, true) // elimina Login del historial
                .build();

        NavHostFragment.findNavController(this)
                .navigate(R.id.action_login_to_home, null, options);
    }
}
```

---

##  PRÁCTICA 5 — AUTH REAL + UX (colocada donde toca)

**Incremental sobre la práctica 4**

Aquí ya tienen:

* Estructura con fragments + nav_graph
* Concepto de sesión
* Validación previa
* Estados UI
* Ejemplo completo de Login real

### Objetivo

Hacer que el flujo deje de ser “fake” y pase a ser **autenticación real** con UX correcta.

### Tareas

1. Conectar proyecto a Firebase e implementar **Auth real**:

   * Login real (email/password)
   * Registro real (email/password)
2. Crear `RegisterFragment` con:

   * Email
   * Password
   * Botón “Crear cuenta”
   * Botón “Volver”
   * ProgressIndicator
3. Validar antes de autenticar:

   * email válido
   * password mínimo 6
4. Estados:

   * loading visible
   * botones desactivados durante loading
5. Errores:

   * campo inválido → `TextInputLayout.setError(...)`
   * error backend → Snackbar
6. Tras login/registro correcto:

   * navegar a Home
   * **eliminar Login/Register del backstack**

### Código de ejemplo (sugerido) para RegisterFragment

(El alumnado debe adaptarlo, no copiar literal: aquí va como guía de referencia técnica)

**Layout recomendado**: igual que `fragment_login.xml`, añadiendo campos necesarios.

**RegisterFragment.java (estructura guía):**

```java
public class RegisterFragment extends Fragment {

    private AuthViewModel vm;

    public RegisterFragment() {
        super(R.layout.fragment_register);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        // 1) findViewById
        // 2) vm con Factory
        // 3) validación previa
        // 4) vm.register(...)
        // 5) observar state: loading/error/success
        // 6) navegar a Home limpiando backstack
        // 7) botón volver -> navigate(action_register_to_login)
    }
}
```

---

## 28. UX/UI aplicada a autenticación: reglas evaluables en DI

Una vez el login funciona, toca hacerlo **usable**.

### 28.1 Qué error va dónde (regla de oro)

* Error de un campo (vacío, formato, longitud) → **en el campo** con `TextInputLayout`
* Error global (credenciales incorrectas, red, servidor) → **Snackbar**

Si todo es Snackbar:

* el usuario no sabe qué corregir
* se rompe la usabilidad

### 28.2 Loading correcto (sin “doble click”)

Durante autenticación:

* botones desactivados
* progress visible
* evitar múltiples envíos

Esto evita:

* crear múltiples peticiones
* estados inconsistentes
* frustración

### 28.3 Navegación limpia tras éxito

Después de autenticación:

* navegar a Home
* impedir volver a Login/Register con atrás

Eso es UX (flujo lógico), no solo “navegación”.

---

## 29. Feedback: Toast vs Snackbar (aplicación práctica)

En apps modernas:

* Toast “flota” fuera del layout
* Snackbar está anclado y es parte de la UI

Regla DI:

> Acciones del usuario → Snackbar
> (no “mensajes sueltos” sin contexto)

Ejemplo de Snackbar:

```java
Snackbar.make(view, "Credenciales incorrectas", Snackbar.LENGTH_LONG).show();
```

---

## 30. Refinamiento de Registro (calidad DI)
<img width="720" height="1280" alt="imagen" src="https://github.com/user-attachments/assets/17312066-c1de-425c-bdf8-0f330e354cfd" />
<img width="1520" height="848" alt="imagen" src="https://github.com/user-attachments/assets/966f9dfb-33c0-4469-9980-cd23f18c0592" />

Registro suele fallar por UX mala si no se cuida:

* confirmar contraseña
* mostrar reglas antes de fallar
* mensajes comprensibles

Ejemplo:

* El nombre de usuario está en uso
* error “Las contraseñas no coinciden” en el campo confirmación

---

##  PRÁCTICA 6 — USABILIDAD DE AUTENTICACIÓN (colocada donde toca)

**Incremental sobre la práctica 5 (refinamiento DI, no funcionalidad nueva)**

### Objetivo

Pasar de “auth que funciona” a “auth usable y profesional”.

### Tareas

1. En `RegisterFragment`, añadir:

   * Campo “Confirmar contraseña”
   * Validar coincidencia
   * Error en el campo confirmación si no coincide
2. Evitar múltiples envíos:

   * desactivar botones en loading
3. Mensajes comprensibles:

   * traducir mensajes “técnicos” a mensajes “de usuario”
   * ejemplo: “Contraseña demasiado débil” en vez de mensajes crudos
4. Revisión DI:

   * jerarquía visual clara
   * acciones principales y secundarias bien diferenciadas
   * feedback consistente (campos + snackbar)
5. Comprobación obligatoria:

   * login correcto → home
   * atrás en home **no vuelve** a login/register

---


