<?php
// ---------------------------------------
// cambiar_password.php
// ---------------------------------------
require_once 'config/db.php';
require_once 'config/auth.php';

requiereAutenticacion();

$db     = getDB();
$userId = (int) $_SESSION['usuario_id'];

$errores  = [];
$mensajes = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $actual   = $_POST['actual']   ?? '';
    $nueva    = $_POST['nueva']    ?? '';
    $confirma = $_POST['confirma'] ?? '';

    // Validaciones de formato ---------------------------------------
    if ($actual === '') {
        $errores[] = 'Debes ingresar tu contraseña actual.';
    }

    if ($nueva === '') {
        $errores[] = 'La nueva contraseña no puede estar vacía.';
    } elseif (strlen($nueva) < 8) {
        $errores[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
    }

    if ($confirma === '') {
        $errores[] = 'Debes confirmar la nueva contraseña.';
    } elseif ($nueva !== $confirma) {
        $errores[] = 'La nueva contraseña y su confirmación no coinciden.';
    }

    if ($nueva === $actual && $nueva !== '') {
        $errores[] = 'La nueva contraseña debe ser diferente a la actual.';
    }

    // Verificar contraseña actual en BD ---------------------------------------
    if (empty($errores)) {
        $stmt = $db->prepare('SELECT password FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($actual, $row['password'])) {
            $errores[] = 'La contraseña actual es incorrecta.';
        }
    }

    // Guardar nueva contraseña ---------------------------------------
    if (empty($errores)) {
        $nuevoHash = password_hash($nueva, PASSWORD_BCRYPT, ['cost' => 12]);

        $upd = $db->prepare('UPDATE usuarios SET password = ? WHERE id = ?');
        $upd->execute([$nuevoHash, $userId]);

        $mensajes[] = '✓ Contraseña actualizada correctamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar contraseña — Sistema de Usuarios</title>
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
  <div class="card">

    <p class="card__logo">Seguridad</p>
    <h1 class="card__title">Cambiar contraseña</h1>
    <p class="card__subtitle">Ingresa tu contraseña actual y define una nueva.</p>

    <!-- Alertas -->
    <?php foreach ($mensajes as $m): ?>
      <div class="alert alert--success"><?= h($m) ?></div>
    <?php endforeach; ?>

    <?php if ($errores): ?>
      <div class="alert alert--error">
        <?php foreach ($errores as $e): ?><div><?= h($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="cambiar_password.php" novalidate>

      <div class="form-group">
        <label for="actual">Contraseña actual</label>
        <input type="password" id="actual" name="actual"
               placeholder="Tu contraseña vigente"
               autocomplete="current-password">
      </div>

      <div class="form-group">
        <label for="nueva">Nueva contraseña</label>
        <input type="password" id="nueva" name="nueva"
               placeholder="Mínimo 8 caracteres"
               autocomplete="new-password">
      </div>

      <div class="form-group">
        <label for="confirma">Confirmar nueva contraseña</label>
        <input type="password" id="confirma" name="confirma"
               placeholder="Repite la nueva contraseña"
               autocomplete="new-password">
      </div>

      <button type="submit" class="btn btn--primary mt-2">Actualizar contraseña</button>
    </form>

    <hr class="divider">
    <p class="text-muted"><a href="perfil.php">← Volver al perfil</a></p>

  </div>
</div>

</body>
</html>
