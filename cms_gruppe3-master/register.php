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

if (isset($_GET['id'])) {
    $userReg->activateUser($_GET['id']);
}
elseif (isset($_POST['submit']) && !empty($_POST['username']) && !empty($_POST['password']) &&
    !empty($_POST['passwordverify']) && !empty($_POST['email'])) {
    if ($_POST['passwordverify'] == $_POST['password']) {
        if(!$userReg->checkUsername()) {
            $ch = curl_init();
            $id = md5(uniqid(rand(), 1));
            curl_setopt($ch, CURLOPT_URL, "https://kark.uit.no/internett/php/mailer/mailer.php?address=" .
                $_POST['email'] . "&url=http://localhost:63342" . $_SERVER['PHP_SELF'] . "?id=" . $id);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);
            $userReg->createUser($id);
            header("Location: login.php");
        } else {
            echo "Username taken";
        }
    } else {
        echo "Passwords do not match";
    }
} else {
    echo $twig->render('register.twig');
}



?>