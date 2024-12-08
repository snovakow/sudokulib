<?php
if (!isset($_GET['start'])) die;
if (!isset($_GET['end'])) die;
if (!isset($_GET['table'])) die;

$start = (int)$_GET['start'];
$end = (int)$_GET['end'];
$table = $_GET['table'];

try {
	$servername = "localhost";
	$username = "snovakow";
	$password = "kewbac-recge1-Fiwpux";
	$dbname = "sudoku";
	$db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

	$stmt = $db->prepare("SELECT `id`, HEX(`puzzleData`) AS 'puzzleData' FROM `$table` WHERE `id`>$start AND `id`<=$end");
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($result);
} catch (PDOException $e) {
	// echo "Error: " . $e->getMessage();
}
