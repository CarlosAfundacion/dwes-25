<?php

session_start();
$db = mysqli_connect('localhost', 'root', '1234', 'web_canciones') or die('Fail');

$nombre = trim($_POST['f_nombre'] ?? '');
$apellidos = trim($_POST['f_apellidos'] ?? '');
$email = trim($_POST['f_email'] ?? '');
$pass1 = $_POST['f_password1'] ?? '';
$pass2 = $_POST['f_password2'] ?? '';

if ($nombre === '' || $apellidos === '' || $email === '' || $pass1 === '' || $pass2 === '') {
  die('<p>Hay campos vacíos.</p><p><a href="/register.html">Volver</a></p>');
}
if ($pass1 !== $pass2) {
  die('<p>Las contraseñas no coinciden.</p><p><a href="/register.html">Volver</a></p>');
}


$stmt = mysqli_prepare($db, "SELECT id FROM tUsuarios WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($res) > 0) {
  die('<p>Ese correo ya está registrado.</p><p><a href="/register.html">Volver</a></p>');
}
mysqli_stmt_close($stmt);

/* Consigue que el usuario se inserte en la base de datos*/

$hash = password_hash($pass1, PASSWORD_DEFAULT);
$stmt2 = mysqli_prepare($db, "INSERT INTO tUsuarios(nombre, apellidos, email, contraseña) VALUES(?,?,?,?)");
mysqli_stmt_bind_param($stmt2, "sis", $nombre, $apellidos, $email, $hash);
mysqli_stmt_execute($stmt2) or die('Error al registrar');
mysqli_stmt_close($stmt2);

mysqli_close($db);

header('Location: /main.php');
exit;
