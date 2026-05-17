<?php
// ---------------------------------------
// login.php  —  Inicio de sesión
// ---------------------------------------
require_once 'config/db.php';
require_once 'config/auth.php';

iniciarSesion();

// Si ya está autenticado, ir directo al perfil
if (!empty($_SESSION['usuario_id'])) {
    header('Location: perfil.php');
    exit;
}

$error = '';

// Mensaje por redirección (sesión expirada, etc.)
if (isset($_GET['error'])) {
    $error = match($_GET['error']) {
        'sesion' => 'Tu sesión ha expirado o no tienes acceso. Inicia sesión.',
        default   => ''
    };
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo   = trim($_POST['correo']   ?? '');
    $password = $_POST['password'] ?? '';

    // Validación ---------------------------------------
    if ($correo === '' || $password === '') {
        $error = 'Por favor, completa todos los campos.';

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del correo no es válido.';

    } else {
        // Buscar usuario en BD ---------------------------------------
        try {
            $db   = getDB();
            $stmt = $db->prepare(
                'SELECT id, cedula, nombre, correo, password
                 FROM usuarios WHERE correo = ? LIMIT 1'
            );
            $stmt->execute([$correo]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password'])) {
                // ✓ Credenciales correctas → crear sesión
                crearSesionUsuario($usuario);
                header('Location: perfil.php');
                exit;
            } else {
                // Mensaje genérico para no revelar si el correo existe
                $error = 'Correo o contraseña incorrectos.';
            }

        } catch (PDOException $e) {
            $error = 'Error de conexión. Intenta más tarde.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — Sistema de Usuarios</title>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>
<div class="wrapper">
  <div class="card">

    <p class="card__logo">Sistema de Usuarios</p>
    <h1 class="card__title">Bienvenido</h1>
    <p class="card__subtitle">Ingresa tus credenciales para continuar.</p>

    <?php if ($error): ?>
      <div class="alert alert--error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>

      <div class="form-group">
        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo"
               placeholder="ana@ejemplo.com" maxlength="150"
               value="<?= h($_POST['correo'] ?? '') ?>"
               autocomplete="email">
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password"
               placeholder="Tu contraseña"
               autocomplete="current-password">
      </div>

      <button type="submit" class="btn btn--primary mt-2">Iniciar sesión</button>
    </form>

    <hr class="divider">
    <p class="text-center text-muted">
      ¿No tienes cuenta? <a href="registro.php">Regístrate</a>
    </p>

  </div>
</div>
</body>
</html>
