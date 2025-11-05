
<html>
  <body>
    <h1>Conversión de temperaturas</h1>
    <?php
    /* Arregla el código para que convierta las temperaturas */
    
     if (isset($_POST["cantidad"]) || isset($_POST["conversion"])) {
        $cantidad = $_POST["cantidad"];
        $conversion = isset($_POST["conversion"]) ? $_POST["conversion"] : "";

        if (empty($cantidad)) {
          echo "Debes introducir una cantidad.<br>";
        } else if (empty($conversion)) {
          echo "Debes seleccionar una conversión.<br>";
        } else {
          
          if ($conversion == "aF") {
            $resultado = $cantidad * 9/5 + 32;
            echo $cantidad . " ºC = " . $resultado . " ºF";
          } else if ($conversion == "aC") {
            $resultado = ($cantidad - 32) * 5/9;
            echo $cantidad . " ºF = " . $resultado . " ºC";
          } else {
            echo "Opción no válida.";
          }
        }
      }
    ?>

    <form action="/temperaturas.php" method="post">
      <label for="cantidad">Cantidad:</label><br>
      <input type="text" id="cantidad" name="cantidad" ><br>

      <input type="radio" id="aF" name="conversion" value="aFar">
      <label for="aF">Celsius → Fahrenheit</label><br>

      <input type="radio" id="aC" name="conversion" value="aCent">
      <label for="aC">Fahrenheit → Celsius</label><br><br>

      <input type="submit" value="Convertir">
    </form>
  </body>
</html>