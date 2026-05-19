<?php 
require __DIR__ . "/../includes/dbQuery.php";
session_start();

$req = $_SERVER["REQUEST_METHOD"];

define('REGEX_LOGIN', '/^[^\s@]+@[^\s@]+\.[^\s@]+$/');   
define('REGEX_NAME', '/^[a-zA-Zа-яА-ЯёЁ\s\-]{2,50}$/u');
define('REGEX_PASSWORD', '/^(?=.*[A-Z])(?=.*[0-9]).{8,}$/');
define('REGEX_CODE', '/^\d{6}$/');

if($req === "POST"){
    if(isset($_POST["signup"])){
        $name = '';
        $surname = '';
        $login = '';
        $password = '';
        $conf_password = '';

        if(isset($_POST["name"])){
            $name = trim($_POST["name"]);
        }
        if(isset($_POST["surname"])){
            $surname = trim($_POST["surname"]);
        }
        if(isset($_POST["login"])){
            $login = trim($_POST["login"]);
        }
        if(isset($_POST["password"])){
            $password = $_POST["password"];
        }
        if(isset($_POST["conf_password"])){
            $conf_password = $_POST["conf_password"];
        }

        if(!empty($name) && !preg_match(REGEX_NAME, $name)){
            $_SESSION["message"] = ["message" => "Invalid name (letters only, 2–50 chars)", "type" => "error"];
            header("Location: ../../index.php"); 
            exit;
        }
        
        if(!preg_match(REGEX_LOGIN, $login)){
            $_SESSION["message"] = ["message" => "Login must be a valid email address", "type" => "error"];
            header("Location: ../../index.php"); 
            exit;
        }
        if(!preg_match(REGEX_PASSWORD, $password)){
            $_SESSION["message"] = ["message" => "Password: min 8 chars, 1 uppercase letter, 1 digit", "type" => "error"];
            header("Location: ../../index.php"); 
            exit;
        }
        if($password !== $conf_password){
            $_SESSION["message"] = ["message" => "Passwords do not match", "type" => "error"];
            header("Location: ../../index.php"); 
            exit;
        }

        $existingLogins = array_column(findIt("idusers", "login"), "login");
        if(in_array($login, $existingLogins)){
            $_SESSION["message"] = ["message" => "This email is already registered", "type" => "error"];
            header("Location: ../../index.php"); 
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $rawCode = rand(111111, 999999);
        $hashedCode = password_hash((string)$rawCode, PASSWORD_BCRYPT);

        $data = [
            "name" => $name,
            "surname" => $surname,
            "login" => $login,
            "password" => $hashedPassword,
            "verifcode" => $hashedCode,
            "verified" => 0
        ];

        insertUser("idusers", $data);

        $_SESSION["email"]          = $login;
        $_SESSION["raw_verif_code"] = $rawCode;
        header("Location: ../pages/verification.php"); 
        exit;
    }

    if(isset($_POST["resend_code"])){
        $login = '';
        if(isset($_SESSION["email"])){
            $login = $_SESSION["email"];
        }
        
        $rawCode = rand(111111, 999999);
        $hashed = password_hash((string)$rawCode, PASSWORD_BCRYPT);
        mysqli_query($conn, "UPDATE `idusers` SET `verifcode` = '$hashed' WHERE `login` = '$login'");
        $_SESSION["raw_verif_code"] = $rawCode;
        header("Location: ../pages/verification.php"); 
        exit;
    }

    if(isset($_POST["verification"])){
        $login = '';
        if(isset($_SESSION["email"])){
            $login = $_SESSION["email"];
        }

        $userCode = '';
        if(isset($_POST["verifCode"])){
            $userCode = trim($_POST["verifCode"]);
        }

        if(!preg_match(REGEX_CODE, $userCode)){
            $_SESSION["message"] = ["message" => "Code must be exactly 6 digits", "type" => "error"];
            header("Location: ../pages/verification.php"); 
            exit;
        }

        $row = findItWhere("idusers", "verifcode", "login", $login);

        if(!empty($row) && password_verify($userCode, $row[0]["verifcode"])) {
            mysqli_query($conn, "UPDATE `idusers` SET `verified` = 1 WHERE `login` = '$login'");
            $_SESSION["message"] = ["message" => "Verification successful!", "type" => "success"];
            header("Location: ../../index.php"); 
            exit;
        } else{
            $_SESSION["message"] = ["message" => "Invalid verification code!", "type" => "error"];
            header("Location: ../pages/verification.php"); 
            exit;
        }
    }

    if(isset($_POST["signin"])){
        $login = '';
        if(isset($_POST["login"])){
            $login = trim($_POST["login"]);
        }

        $password = '';
        if(isset($_POST["password"])){
            $password = $_POST["password"];
        }

        if(!preg_match(REGEX_LOGIN, $login)){
            $_SESSION["message"] = ["message" => "Login must be a valid email address", "type" => "error"];
            header("Location: ../../index.php"); 
            exit;
        }

        $users = findItWhere("idusers", "id, login, password, verified", "login", $login);

        if(!empty($users) && password_verify($password, $users[0]["password"])){
            if((int)$users[0]["verified"] === 0){
                $_SESSION["email"] = $login;
                $rawCode = rand(111111, 999999);
                $hashed  = password_hash((string)$rawCode, PASSWORD_BCRYPT);
                mysqli_query($conn, "UPDATE `idusers` SET `verifcode` = '$hashed' WHERE `login` = '$login'");
                $_SESSION["raw_verif_code"] = $rawCode;
                $_SESSION["message"] = ["message" => "Please verify your account first", "type" => "error"];
                header("Location: ../pages/verification.php"); 
                exit;
            }

            $_SESSION["user"] = ["id" => $users[0]["id"], "login" => $users[0]["login"]];
            header("Location: ../pages/profile.php"); 
            exit;
        } else {
            $_SESSION["message"] = ["message" => "Invalid login or password!", "type" => "error"];
            header("Location: ../../index.php"); 
            exit;
        }
    }


    if(isset($_POST["create_post"])){
        if(!isset($_SESSION["user"])){
            header("Location: ../../index.php");
            exit;
        }

        $content = isset($_POST["content"]) ? trim($_POST["content"]) : '';

        if(empty($content)){
            $_SESSION["message"] = ["message" => "Post content cannot be empty", "type" => "error"];
            header("Location: ../pages/posts.php");
            exit;
        }
        
        if(mb_strlen($content) > 255){
            $_SESSION["message"] = ["message" => "Post is too long (max 255 characters)", "type" => "error"];
            header("Location: ../pages/posts.php");
            exit;
        }

        insertPost($_SESSION["user"]["id"], $content);
        $_SESSION["message"] = ["message" => "Post published!", "type" => "success"];
        header("Location: ../pages/posts.php");
        exit;
    }

    if(isset($_POST["delete_post"])){
        if(!isset($_SESSION["user"])){
            header("Location: ../../index.php");
            exit;
        }

        $postId = isset($_POST["post_id"]) ? (int)$_POST["post_id"] : 0;
        if($postId > 0){
            deletePost($postId, $_SESSION["user"]["id"]);
        }
        header("Location: ../pages/posts.php");
        exit;
    }
}