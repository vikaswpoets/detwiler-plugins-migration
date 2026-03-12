<?php
//$servername = "localhost";
//$username = "username";
//$password = "password";
$servername='10.169.1.152';
$database='stg_pim_gi';
$username='gi-external';
$password='20240926%Prod!User';


$i=0;
//for ($i=0;$i<500;$i++){
// Create connection
$conn[$i] = new mysqli($servername, $username, $password);

// Check connection
if ($conn[$i]->connect_error) {
  die("Connection failed: " . $conn[$i]->connect_error);
}
echo "Connected successfully";
//}
?>
