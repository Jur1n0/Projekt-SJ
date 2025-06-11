<?php
session_start();
session_unset(); // Odstráni všetky premenné relácie
session_destroy(); // Zničí reláciu
header("Location: login.php"); // Presmeruj na prihlasovaciu stránku
exit();
?>