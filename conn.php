<?php 
$hostname = "localhost"; //local hist
$database = "buy"; //database name 
$username = "root"; //user
$password = ""; //password
$con = mysqli_connect($hostname, $username, $password, $database); 
if (mysqli_connect_errno()) 
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit();
}
?>

