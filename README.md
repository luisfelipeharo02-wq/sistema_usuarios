# Sistema de Perfil de Usuario y Cambio de Contraseña
## PHP + MySQL — Documentación

---

## Estructura del proyecto

```
sistema_usuarios/
├── config/
│   ├── db.php              # Conexión PDO
│   └── auth.php            # Helpers de sesión y seguridad
├── assets/
│   └── css/
│       └── estilo.css      # Estilos globales
├── registro.php            # Registro de nuevos usuarios
├── login.php               # Inicio de sesión
├── perfil.php              # Zona privada (ver y editar perfil)
├── cambiar_password.php    # Cambio seguro de contraseña
├── logout.php              # Cierre de sesión
```

---

## Requisitos

| Herramienta | Versión mínima |
|-------------|----------------|
| PHP         | 8.0+           |
| MySQL       | 5.7+ / MariaDB 10.3+ |
| Servidor    | Apache/Nginx con mod_rewrite o XAMPP/MAMP |

---

## Instalación paso a paso

### 1. Crear la base de datos

**phpMyAdmin**.

### 2. Configurar la conexión

Edita `config/db.php` y ajusta:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_usuarios');
define('DB_USER', 'root');      
define('DB_PASS', '');        
```

### 3. Servir el proyecto

Copia la carpeta en la raíz de tu servidor web:
- **XAMPP**: `C:/xampp/htdocs/sistema_usuarios/`
- **MAMP**: `/Applications/MAMP/htdocs/sistema_usuarios/`

Accede en el navegador:
```
http://localhost/sistema_usuarios/registro.php
```

---

## Flujo de la aplicación

```
registro.php  ──→  login.php  ──→  perfil.php
                                      │
                                      ├──→ cambiar_password.php
                                      │
                                      └──→ logout.php ──→ login.php
```

---

## Decisiones de seguridad implementadas

### Contraseñas
- `password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12])` para almacenamiento.
- `password_verify($input, $hash)` para validación; nunca se compara texto plano.
- La nueva contraseña debe diferir de la actual (verificado en servidor).

### Sesiones
- `session_regenerate_id(true)` al iniciar sesión → previene *session fixation*.
- Cookies con `httponly: true` y `samesite: Strict`.
- `requiereAutenticacion()` en todas las páginas privadas redirige a login si no hay sesión.
- `destruirSesion()` limpia `$_SESSION`, invalida la cookie y destruye el archivo de sesión.

### Entradas de usuario
- `trim()` en todos los campos de texto.
- `filter_var($email, FILTER_VALIDATE_EMAIL)` para correos.
- Consultas con PDO + sentencias preparadas (sin concatenación de SQL).
- Salida HTML siempre escapada con `htmlspecialchars()` (función `h()`).
- Mensajes de error genéricos en login (no revelan si el correo existe).

---
