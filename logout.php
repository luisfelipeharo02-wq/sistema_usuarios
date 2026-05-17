<?php
// ---------------------------------------
// logout.php  —  Cierre de sesión
// ---------------------------------------
require_once 'config/auth.php';

destruirSesion();

header('Location: login.php');
exit;
