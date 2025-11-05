<?php
session_start();
$db = mysqli_connect('localhost', 'root', '1234', 'web_canciones') or die('Fail');

$query = "SELECT id, titulo, artista, url_imagen FROM tCanciones";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

echo '<!DOCTYPE html>';
echo '<html lang="es">';
echo '<head>';
echo '  <meta charset="UTF-8">';
echo '  <title>Listado de canciones</title>';
echo '  <style>
    body { font-family: system-ui, sans-serif; margin: 24px; }
    header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
    nav a { margin-left:12px; }
    .list { display:grid; grid-template-columns: repeat(auto-fill, minmax(260px,1fr)); gap:16px; }
    .item {
      display:flex; gap:12px; align-items:center;
      border:1px solid #ddd; padding:12px; border-radius:10px;
      transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease;
      opacity: 0.95;
    }
    .item:hover { transform: translateY(-3px); box-shadow: 0 8px 18px rgba(0,0,0,.1); opacity: 1; }
    img.thumb { width:80px; height:80px; object-fit:cover; border-radius:8px; }
    .title { margin:0; }
    .artist { margin:0; color:#555; font-size:.95em; }
  </style>';
echo '</head>';
echo '<body>';

echo '<header>';
echo '  <h1>Listado de canciones</h1>';
echo '  <nav>';
if (!empty($_SESSION['user_id'])) {
 
  echo '    <a href="/logout.php">Logout</a>';
} else {
  echo '    <a href="/register.html">Registro</a>';
  echo '    <a href="/login.html">Login</a>';
}
echo '  </nav>';
echo '</header>';
/*Haz que se listen todas las canciones*/
echo '<div class="list">';
while () {
  echo '  <a class="item" href="/detail.php?cancion_id=' .  . '">';
  echo '    <img class="thumb" src="' . . '" alt="cover">';
  echo '    <div>';
  echo '      <h3 class="title">' .  . '</h3>';
  echo '      <p class="artist">' .  . '</p>';
  echo '    </div>';
  echo '  </a>';
}
echo '</div>';

mysqli_stmt_close($stmt);
mysqli_close($db);

echo '</body>';
echo '</html>';
?>
