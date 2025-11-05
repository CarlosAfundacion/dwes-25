
<?php
/*TODO OK*/
session_start();
session_destroy();
header('Location: /login.html');
exit;
?>