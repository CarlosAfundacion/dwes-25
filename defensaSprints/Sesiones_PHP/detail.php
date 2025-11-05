<?php
/* Haz que se muestre el id del usuario logueado sin hacer ninguna consulta a la BD*/
session_start();
$db = mysqli_connect('localhost', 'root', '1234', 'web_canciones') or die('Fail');

if (!isset($_GET['cancion_id'])) { die('Falta cancion_id'); }
$cancion_id = (int) $_GET['cancion_id'];

$stmt = mysqli_prepare($db, "SELECT titulo, artista, url_imagen FROM tCanciones WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $cancion_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$song = mysqli_fetch_assoc($res);
if (!$song) { die('Canción no encontrada'); }

$query = "
  SELECT c.comentario, c.fecha, u.nombre, u.apellidos
  FROM tComentarios c
  LEFT JOIN tUsuarios u ON c.usuario_id = u.id
  WHERE c.cancion_id = ?
";
$stmt2 = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt2, "i", $cancion_id);
mysqli_stmt_execute($stmt2);
$res2 = mysqli_stmt_get_result($stmt2);

echo '<!DOCTYPE html>';
echo '<html lang="es">';
echo '<head>';
echo '  <meta charset="UTF-8">';
echo '  <title>' . $song['titulo'] . '</title>';
echo '  <style>
    body { font-family: system-ui, sans-serif; margin: 24px; max-width: 900px; }
    header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
    nav a { margin-left:12px; }
    img.cover { width:180px; height:180px; object-fit:cover; border-radius:8px; }
    .comment { border-bottom:1px solid #eee; padding:8px 0; }
    .muted { color:#666; font-size:0.9em; }
  </style>';
echo '</head>';
echo '<body>';

echo '  <header>';
echo '    <h1>' . $song['titulo'] . '</h1>';
echo '    <nav>';
echo '      <a href="/main.php">Volver</a>';

echo '    </nav>';
echo '  </header>';

echo '  <h2>' . $song['artista'] . '</h2>';
echo '  <img class="cover" src="' . $song['url_imagen'] . '" alt="cover"><br>';
/*Consigue que se muestren los comentarios*/

echo '  <h3>Comentarios</h3>';
while ($row = mysqli_fetch_assoc($res2)) {
  echo '  <div class="comment">';
  echo '    <p>' . $row[1] . '</p>';
  echo '    <p class="muted">';
  if ($row[2]) {
    echo $row[2] . ' ' . $row[3];
  } else {
    echo 'Anónimo';
  }
  
  echo ' — ' . $row[4];
  echo '    </p>';
  echo '  </div>';
}

echo '  <h3>Añadir comentario</h3>';
echo '  <form action="/comment.php" method="post">';
echo '    <input type="hidden" name="cancion_id" value="' . $cancion_id . '">';
echo '    <textarea name="new_comment" rows="3" cols="60" required></textarea><br>';
echo '    <input type="submit" value="Enviar comentario">';
echo '  </form>';

mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt2);
mysqli_close($db);

echo '</body>';
echo '</html>';
