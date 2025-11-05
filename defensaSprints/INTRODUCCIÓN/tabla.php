<html>
  <body>
    <h1>Tabla del 7</h1>
    <table border="1">
      <tr><th>Expresión</th><th>Resultado</th></tr>
      <?php
      /*Cambia el for por un while*/
        for ($i = 1; $i <= 10; $i = $i + 1) {
          $res = 7 * $i;
          echo "<tr>";
          echo "<td>7 x " . $i . "</td>";
          echo "<td>" . $res . "</td>";
          echo "</tr>";
        }
      ?>
    </table>
  </body>
</html>