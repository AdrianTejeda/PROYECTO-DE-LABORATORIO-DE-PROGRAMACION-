<!-- Cierra la seccion y vuelve al login-->
<?php
require_once __DIR__ . '/config.php';
session_destroy();
header('Location: login.php');
