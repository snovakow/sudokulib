<?php

// header("Access-Control-Allow-Origin: *");
if (!isset($_GET['start'])) die;
if (!isset($_GET['end'])) die;
if (!isset($_GET['table'])) die;

$start = (int)$_GET['start'];
$end = (int)$_GET['end'];
$table = $_GET['table'];

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

try {
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	// $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_STRINGIFY_FETCHES);

	$stmt = $conn->prepare("SELECT 
		`id`, HEX(`puzzleData`) AS 'puzzleData', `clueCount`, `simple`, `bruteForce`, 
		`solveType`, `naked2`, `naked3`, `naked4`, `hidden2`, `hidden3`, `hidden4`, 
		`omissions`, `uniqueRectangle`, `yWing`, `xyzWing`, `xWing`, `swordfish`, `jellyfish`, 
		`has_naked2`, `has_naked3`, `has_naked4`, `has_hidden2`, `has_hidden3`, `has_hidden4`, 
		`has_omissions`, `has_uniqueRectangle`, `has_yWing`, `has_xyzWing`, 
		`has_xWing`, `has_swordfish`, `has_jellyfish` 
		FROM `" . $table . "` WHERE `id`>" . $start . " AND `id`<=" . $end);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($result);
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}
$conn = null;
