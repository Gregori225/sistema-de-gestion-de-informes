<?php
session_start();
session_destroy(); // Elimina toda la información de la sesión actual
header("location: login.php"); // Nos manda de vuelta al login
exit();
