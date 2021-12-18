<?php


class UserRegistration
{
    private $db;
    private $twig;


    function __construct(PDO $db, $twig)
    {
        $this->db = $db;
        $this->twig = $twig;
        require_once 'vendor/autoload.php';
    }

    function checkUsername(): bool
    {
        $taken = $this->db->query("SELECT PK_User FROM CMS_User WHERE username = '" . $_POST['username'] . "'");
        if ($taken->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    function createUser($id)
    {
        try {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $token = $id;
            $stmt = $this->db->prepare("    INSERT INTO `CMS_User` (
                                                        `username`,
                                                        `password`,
                                                        `email`,
                                                        `datecreated`,
                                                        `verification`,
                                                        `activated`
                                                    ) VALUES (
                                                        :username,
                                                        :password,
                                                        :email,
                                                        NOW(),
                                                        :token,
                                                        0
                                                    )");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $result = $stmt->execute();
            if ($result) {
            } else {
                echo "Something went wrong";
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    function activateUser($token)
    {
        try {
            $result = $this->db->query("UPDATE CMS_User SET activated = 1, verification = '' WHERE PK_User = (
                                                    SELECT PK_User FROM (
                                                        SELECT * FROM CMS_User) AS T 
                                                    WHERE verification = '" . $token . "')");
            if ($result) {
                echo $this->twig->render('register.twig', array('par' => $result));
            } else {
                echo $this->twig->render('register.twig');
            }

            echo "Your user is now activated";
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    function logIn() {
        try {
            $username = $_POST['username'];
            $result = $this->db->query("SELECT PK_User, activated, password, user_type FROM CMS_User WHERE username = '". $username . "'");
            $vars = $result->fetch(PDO::FETCH_ASSOC);
            if (password_verify($_POST['password'], $vars['password']) && $vars['user_type'] == 'admin') {
                echo "success";
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $vars['PK_User'];
                $_SESSION['username'] = $username;
                $_SESSION['user_type'] = $vars['user_type'];
                header('Location: admin_account.php');
            } else if (password_verify($_POST['password'], $vars['password']) && $vars['activated'] == 1) {
                echo "success";
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $vars['PK_User'];
                $_SESSION['username'] = $username;
                $_SESSION['user_type'] = $vars['user_type'];
                header('Location: account.php');
            } else {
                echo "Error! Your login or password was incorrect. Please try again. ";
                echo $this->twig->render('login.twig', array('username' => $username));
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}