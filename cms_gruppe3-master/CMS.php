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

    if (isset($_POST['add'])) {
        $docReg->addFolder();
    }
    else if (isset($_GET['viewId'])) {
        $docReg->viewPost($_GET['viewId']);
    }
    else if (isset($_GET['id']) && $_GET['delete'] == true) {
        $docReg->deletePost();
        header("Location: CMS.php");
    }
    else if (isset($_POST['search'])) {
        header("Location: CMS.php?search=" . $_POST['searchstring']);
    }
    else if (isset($_GET['search'])) {
        $docReg->searchEngine();
    }
    else {
        $docReg->viewAll();
    }
    if (isset($_POST['commentSubmit'])) {
        $docReg->addComment();
    }

    if (isset($_POST['edit'])) {
        $docReg->editComment();
    }
    if (isset($_POST['delete'])) {
        $docReg->deleteComment();
    }
    ?>


