<?php
if (!isset($_GET['version'])) die();
$version = (int)$_GET['version'];
if ($version !== 1) die();

try {
	$servername = "localhost";
	$username = "snovakow";
	$password = "kewbac-recge1-Fiwpux";
	$dbname = "sudoku";
	$db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

	$stmt = $db->prepare("SELECT `tableCount`, `puzzleCount` FROM `tables`");
	$stmt->execute();
	$result = $stmt->fetch();

	$tableCount = $result["tableCount"];
	$puzzleCount = $result["puzzleCount"];
	echo  "$tableCount:$puzzleCount";
} catch (PDOException $e) {
	// echo "Error: " . $e->getMessage();
}
