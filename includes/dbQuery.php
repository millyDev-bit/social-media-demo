<?php 
require __DIR__ .  "/../config/db.php";

function findIt($tb, $focus){
    global $conn;
    $query  = "SELECT $focus FROM `$tb`";
    $answer = mysqli_query($conn, $query);
    $array  = [];
    foreach($answer as $value){
        $array[] = $value;
    }
    return $array;
}

function findItWhere($tb, $focus, $where, $value){
    global $conn;
    $value  = mysqli_real_escape_string($conn, $value);
    $query  = "SELECT $focus FROM `$tb` WHERE `$where`='$value'";
    $answer = mysqli_query($conn, $query);
    $array  = [];
    foreach($answer as $row){
        $array[] = $row;
    }
    return $array;
}

function insertUser($tb, $vals){
    global $conn;

    $name = mysqli_real_escape_string($conn, $vals["name"]);
    $surname = mysqli_real_escape_string($conn, $vals["surname"]);
    $login = mysqli_real_escape_string($conn, $vals["login"]);
    $password = mysqli_real_escape_string($conn, $vals["password"]);
    $verifcode = mysqli_real_escape_string($conn, $vals["verifcode"]);
    $verified = (int)$vals["verified"];

    $query = "INSERT INTO `$tb` 
              (`name`, `surname`, `login`, `password`, `verifcode`, `verified`) 
              VALUES ('$name', '$surname', '$login', '$password', '$verifcode', '$verified')";

    return mysqli_query($conn, $query);
}

function insertPost($userid, $content){
    global $conn;
    $userid = (int)$userid;
    $content = mysqli_real_escape_string($conn, $content);
    $date = date("Y-m-d");
    $query = "INSERT INTO `post` (`userid`, `content`, `date`) VALUES ('$userid', '$content', '$date')";
    return mysqli_query($conn, $query);
}


function getAllPosts(){
    global $conn;
    $query = "SELECT p.id, p.content, p.date, p.userid,
                     u.name, u.surname
              FROM `post` p
              JOIN `idusers` u ON p.userid = u.id
              ORDER BY p.id DESC";
    $answer = mysqli_query($conn, $query);
    $array = [];
    foreach($answer as $row){
        $array[] = $row;
    }
    return $array;
}

function getPostsByUser($userid){
    global $conn;
    $userid = (int)$userid;
    $query = "SELECT p.id, p.content, p.date, p.userid,
                      u.name, u.surname
               FROM `post` p
               JOIN `idusers` u ON p.userid = u.id
               WHERE p.userid = '$userid'
               ORDER BY p.id DESC";
    $answer = mysqli_query($conn, $query);
    $array = [];
    foreach($answer as $row){
        $array[] = $row;
    }
    return $array;
}

function deletePost($postId, $userId){
    global $conn;
    $postId = (int)$postId;
    $userId = (int)$userId;
    $query = "DELETE FROM `post` WHERE `id`='$postId' AND `userid`='$userId'";
    return mysqli_query($conn, $query);
}

?>