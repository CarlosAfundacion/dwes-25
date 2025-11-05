<html>

<body>
  <h1>Calculadora</h1>
  <?php
if (isset($_POST["op"])) {
  $a = $_POST["operando 1"];
  $b = $_POST["operando 2"];
  $op = $_POST["op"];
/* 
  Validar que los operandos no estén vacíos:
  Debe de mostrar  Falta operando 1 o Falta operando 2 según corresponda.
 */
  if (empty($a) || empty($b)) {
    echo "Falta un operando.";
  } else {
    switch ($op) {
      case "suma":
        echo $a . " + " . $b . " = " . ($a + $b);
        break;
      case "resta":
        echo $a . " - " . $b . " = " . ($a - $b);
        break;
      case "mult":
        echo $a . " * " . $b . " = " . ($a * $b);
        break;
      case "div":
        echo $a . " / " . $b . " = " . ($a / $b);
        break;
      case "":
        echo "Debes seleccionar una operación.";
        break;
      default:
        echo "Operación no válida.";
    }
  }
}
?>


  <form action="/calc.php" method="post">
    <label for="a">Primer número:</label><br>
    <input type="text" id="operando 1" name="a"><br>

    <label for="b">Segundo número:</label><br>
    <input type="text" id="operando 2 name="b"><br>

    <label for="op">Operación:</label><br>
    <select id="op" name="op">
      <option value="" selected>
      <option value="suma">Suma</option>
      <option value="resta">Resta</option>
      <option value="mult">Multiplicación</option>
      <option value="div">División</option>
    </select><br><br>

    <input type="submit" value="Calcular">
  </form>
</body>

</html>