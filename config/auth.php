<?php
// ---------------------------------------
// Auth (sesión)
// ---------------------------------------

/**
 * Inicia la sesión si aún no está activa.
 * Configura parámetros de seguridad.
 */
function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => false,   //true en producción con HTTPS
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

/**
 * Redirige al login si no hay sesión activa.
 * Debe llamarse al inicio de cada página privada.
 */
function requiereAutenticacion(): void
{
    iniciarSesion();
    if (empty($_SESSION['usuario_id'])) {
        header('Location: login.php?error=sesion');
        exit;
    }
}

/**
 * Registra los datos del usuario en la sesión después del login.
 */
function crearSesionUsuario(array $usuario): void
{
    session_regenerate_id(true);  // previene session fixation
    $_SESSION['usuario_id']     = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_correo'] = $usuario['correo'];
}

/**
 * Destruir completamente la sesión.
 */
function destruirSesion(): void
{
    iniciarSesion();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Escapa salida HTML para prevenir XSS.
 */
function h(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
