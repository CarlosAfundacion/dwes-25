<?php

session_start();
$db = mysqli_connect('localhost', 'root', '1234', 'web_canciones') or die('Fail');

$email_posted = $_POST['f_email'] ?? '';
$password_posted = $_POST['f_password'] ?? '';

$stmt = mysqli_prepare($db, "SELECT id, contraseña FROM tUsuarios WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email_posted);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) > 0) {
  $row = mysqli_fetch_assoc($res);
  /*Comprueba que la contraseña es correcta*/
  if (password_verify()) {
    $_SESSION['user_id'] = (int)$row['id'];
    mysqli_stmt_close($stmt);
    mysqli_close($db);
    header('Location: /main.php');
    exit;
  } else {
    echo '<p>Contraseña incorrecta</p><p><a href="/login.html">Volver</a></p>';
  }
} else {
  echo '<p>Usuario no encontrado con ese email</p><p><a href="/login.html">Volver</a></p>';
}

mysqli_stmt_close($stmt);
mysqli_close($db);
