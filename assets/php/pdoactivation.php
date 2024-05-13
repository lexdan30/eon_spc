<?php
$servername = "localhost";
$username = "root";
$password = "root";

try {
  $conn = new PDO("mysql:host=$servername;dbname=kajima", $username, $password,array(PDO::MYSQL_ATTR_LOCAL_INFILE => true,PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
   echo "Connection failed: " . $e->getMessage();
}



?>