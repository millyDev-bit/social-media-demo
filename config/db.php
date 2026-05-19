<?php

$host = "your localhost";
$user = "your root";
$pass = "your password";
$dbname = "your db name";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if(!$conn){
    die("Error");
}