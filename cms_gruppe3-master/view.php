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

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $id = intval($_GET['id']);
    $docReg->viewDocument($id);
}
if (isset($_POST['commentSubmit'])) {
    $docReg->addComment();
}
?>