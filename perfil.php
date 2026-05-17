<?php
// ---------------------------------------
// Zona privada: ver y editar perfil
// ---------------------------------------
require_once 'config/db.php';
require_once 'config/auth.php';

requiereAutenticacion();   // redirige a login si no hay sesión

$db     = getDB();
$userId = (int) $_SESSION['usuario_id'];

// Cargar datos desde BD ---------------------------------------
$stmt = $db->prepare('SELECT cedula, nombre, correo, fecha_registro FROM usuarios WHERE id = ?');
$stmt->execute([$userId]);
$usuario = $stmt->fetch();

if (!$usuario) {
    // Sesión huérfana (usuario eliminado)
    destruirSesion();
    header('Location: login.php');
    exit;
}

$mensajes = [];
$errores  = [];

// Procesar actualización de perfil ---------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    // Validar nombre
    if ($nombre === '') {
        $errores[] = 'El nombre no puede estar vacío.';
    } elseif (mb_strlen($nombre) < 2) {
        $errores[] = 'El nombre debe tener al menos 2 caracteres.';
    } elseif (mb_strlen($nombre) > 100) {
        $errores[] = 'El nombre no puede superar los 100 caracteres.';
    }

    // Validar correo
    if ($correo === '') {
        $errores[] = 'El correo no puede estar vacío.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El formato del correo no es válido.';
    } else {
        // Verificar que el correo nuevo no pertenezca a otro usuario
        $chk = $db->prepare('SELECT id FROM usuarios WHERE correo = ? AND id != ? LIMIT 1');
        $chk->execute([$correo, $userId]);
        if ($chk->fetch()) {
            $errores[] = 'Ese correo ya lo usa otra cuenta.';
        }
    }

    if (empty($errores)) {
        $upd = $db->prepare('UPDATE usuarios SET nombre = ?, correo = ? WHERE id = ?');
        $upd->execute([$nombre, $correo, $userId]);

        // Actualizar datos en sesión
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_correo'] = $correo;

        // Refrescar variable local
        $usuario['nombre'] = $nombre;
        $usuario['correo'] = $correo;

        $mensajes[] = '✓ Perfil actualizado correctamente.';
    }
}

$fechaRegistro = date('d/m/Y', strtotime($usuario['fecha_registro']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil — Sistema de Usuarios</title>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>

<!-- Barra de navegación ----------------------------------------->
<nav class="nav">
  <span class="nav__brand">Sistema de Usuarios</span>
  <div class="nav__links">
    <a href="perfil.php" class="btn btn--ghost">Perfil</a>
    <a href="cambiar_password.php" class="btn btn--ghost">Contraseña</a>
    <a href="logout.php" class="btn btn--danger">Cerrar sesión</a>
  </div>
</nav>

<div class="wrapper">
  <div class="card card--wide">

    <p class="card__logo">Zona privada</p>
    <h1 class="card__title">Mi perfil</h1>
    <p class="card__subtitle">Hola, <?= h($usuario['nombre']) ?>. Aquí puedes actualizar tus datos.</p>

    <!-- Alertas -->
    <?php foreach ($mensajes as $m): ?>
      <div class="alert alert--success"><?= h($m) ?></div>
    <?php endforeach; ?>

    <?php if ($errores): ?>
      <div class="alert alert--error">
        <?php foreach ($errores as $e): ?><div><?= h($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Datos actuales (solo lectura) -->
    <div style="margin-bottom:1.5rem;">
      <div class="info-row">
        <span class="info-row__label">Cédula</span>
        <span><?= h($usuario['cedula']) ?></span>
      </div>
      <div class="info-row">
        <span class="info-row__label">Miembro desde</span>
        <span><?= h($fechaRegistro) ?></span>
      </div>
    </div>

    <hr class="divider">

    <!-- Formulario de actualización -->
    <form method="POST" action="perfil.php" novalidate>

      <div class="form-group">
        <label for="nombre">Nombre completo</label>
        <input type="text" id="nombre" name="nombre"
               value="<?= h($usuario['nombre']) ?>"
               maxlength="100" required>
      </div>

      <div class="form-group">
        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo"
               value="<?= h($usuario['correo']) ?>"
               maxlength="150" required>
      </div>

      <button type="submit" class="btn btn--primary mt-2">Guardar cambios</button>
    </form>

    <hr class="divider">
    <p class="text-muted">
      ¿Quieres cambiar tu contraseña?
      <a href="cambiar_password.php">Ir a cambio de contraseña</a>
    </p>

  </div>
</div>

</body>
</html>
