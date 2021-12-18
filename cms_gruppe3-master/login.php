<?php
session_start();
spl_autoload_register(function ($class_name) {
    require_once 'classes/' . $class_name . '.class.php';
});
require_once 'auth_pdo.php';
require_once 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

$userReg = new UserRegistration($db, $twig);


if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && $_SESSION['user_type'] == 'admin') {
    header('location: admin_account.php');
    exit;
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    header('Location: account.php');
    exit;
}

if (isset($_POST['login']) && !empty($_POST['username']) && !empty($_POST['password'])) {
    $userReg->checkUsername();
    $userReg->logIn();
} else {
    echo $twig->render('login.twig');
}
?>