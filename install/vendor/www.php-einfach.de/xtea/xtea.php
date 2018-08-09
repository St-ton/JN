<?php
//Usage of the blowfish class
include("xtea.class.php");



$xtea = new XTEA("secret Key");
$cipher = $xtea->Encrypt("Hello World");
$plain = $xtea->Decrypt($cipher);

echo "Encrypted: $cipher <br>";
echo "Decrypted: $plain";


echo "<br><br>Benchmark:<br> ";
$xtea->benchmark();


echo "<br>";
//Check the implementation
if(!$xtea->check_implementation())
	echo "<b>The implementation of the algorithm contains error!</b>";

?>