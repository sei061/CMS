<?php
session_start();
spl_autoload_register(function ($class_name) {
    require_once 'classes/' . $class_name . '.class.php';
});

require_once 'auth_pdo.php';
require_once 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

$docReg = new DocumentRegistration($db, $twig);

if (isset($_POST['submit']) && !empty($_FILES['file']['name'])) {
    $docReg->uploadDocument();
    echo "<p>Document is uploaded</p>";
} else {
    echo $twig->render('upload.twig', array('loggedin' => (isset($_SESSION['loggedin']) ? true : false)));
}?>