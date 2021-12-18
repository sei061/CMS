<?php
spl_autoload_register(function ($class_name) {
    require_once 'classes/' . $class_name . '.class.php';
});

require_once 'auth_pdo.php';
require_once 'vendor/autoload.php';

session_start();
$_SESSION['username'];

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);
echo $twig->render('account.twig', ['username' => $_SESSION['username']]);







?>