<?php
// ---------------------------------------
// registro.php  —  Registro de nuevos usuarios
// ---------------------------------------
require_once 'config/db.php';
require_once 'config/auth.php';

iniciarSesion();

// Si ya está autenticado, redirigir al perfil
if (!empty($_SESSION['usuario_id'])) {
    header('Location: perfil.php');
    exit;
}

$errores  = [];
$exito    = false;
$campos   = ['cedula' => '', 'nombre' => '', 'correo' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Inputs ---------------------------------------
    $cedula   = trim($_POST['cedula']   ?? '');
    $nombre   = trim($_POST['nombre']   ?? '');
    $correo   = trim($_POST['correo']   ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';

    $campos = compact('cedula', 'nombre', 'correo');

    // Validaciones ---------------------------------------
    if ($cedula === '') {
        $errores[] = 'La cédula es obligatoria.';
    } elseif (!preg_match('/^\d{10,13}$/', $cedula)) {
        $errores[] = 'La cédula debe contener entre 10 y 13 dígitos.';
    }

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    } elseif (mb_strlen($nombre) < 2) {
        $errores[] = 'El nombre debe tener al menos 2 caracteres.';
    }

    if ($correo === '') {
        $errores[] = 'El correo es obligatorio.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El formato del correo no es válido.';
    }

    if ($password === '') {
        $errores[] = 'La contraseña es obligatoria.';
    } elseif (strlen($password) < 8) {
        $errores[] = 'La contraseña debe tener mínimo 8 caracteres.';
    }

    if ($confirm === '') {
        $errores[] = 'Debes confirmar la contraseña.';
    } elseif ($password !== $confirm) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    // Verificar datos en BD solo si no hay errores previos ---------------------------------------
    if (empty($errores)) {
        try {
            $db  = getDB();

            // Correo duplicado
            $stmt = $db->prepare('SELECT id FROM usuarios WHERE correo = ? LIMIT 1');
            $stmt->execute([$correo]);
            if ($stmt->fetch()) {
                $errores[] = 'Ese correo ya está registrado. ¿Deseas <a href="login.php">iniciar sesión</a>?';
            }

            // Cédula duplicada
            $stmt = $db->prepare('SELECT id FROM usuarios WHERE cedula = ? LIMIT 1');
            $stmt->execute([$cedula]);
            if ($stmt->fetch()) {
                $errores[] = 'Esa cédula ya está registrada.';
            }

        } catch (PDOException $e) {
            $errores[] = 'Error de conexión con la base de datos.';
        }
    }

    // Insertar usuario ---------------------------------------
    if (empty($errores)) {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            $stmt = $db->prepare(
                'INSERT INTO usuarios (cedula, nombre, correo, password)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$cedula, $nombre, $correo, $hash]);

            $exito  = true;
            $campos = ['cedula' => '', 'nombre' => '', 'correo' => ''];

        } catch (PDOException $e) {
            $errores[] = 'No se pudo guardar el usuario. Intenta de nuevo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro — Sistema de Usuarios</title>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>
<div class="wrapper">
  <div class="card">

    <p class="card__logo">Sistema de Usuarios</p>
    <h1 class="card__title">Crear cuenta</h1>
    <p class="card__subtitle">Completa los datos para registrarte.</p>

    <?php if ($exito): ?>
      <div class="alert alert--success">
        ✓ Cuenta creada correctamente. <a href="login.php">Inicia sesión</a>.
      </div>
    <?php endif; ?>

    <?php if ($errores): ?>
      <div class="alert alert--error">
        <?php foreach ($errores as $e): ?>
          <div><?= $e /* HTML intencional en errores internos */ ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="registro.php" novalidate>

      <div class="form-group">
        <label for="cedula">Cédula</label>
        <input type="text" id="cedula" name="cedula"
               value="<?= h($campos['cedula']) ?>"
               placeholder="Ej. 1234567890" maxlength="13">
      </div>

      <div class="form-group">
        <label for="nombre">Nombre completo</label>
        <input type="text" id="nombre" name="nombre"
               value="<?= h($campos['nombre']) ?>"
               placeholder="Juan Pérez" maxlength="100">
      </div>

      <div class="form-group">
        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo"
               value="<?= h($campos['correo']) ?>"
               placeholder="nombre@ejemplo.com" maxlength="150">
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password"
               placeholder="Mínimo 8 caracteres">
      </div>

      <div class="form-group">
        <label for="confirm">Confirmar contraseña</label>
        <input type="password" id="confirm" name="confirm"
               placeholder="Repite la contraseña">
      </div>

      <button type="submit" class="btn btn--primary mt-2">Registrarme</button>
    </form>

    <hr class="divider">
    <p class="text-center text-muted">
      ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
    </p>

  </div>
</div>
</body>
</html>
