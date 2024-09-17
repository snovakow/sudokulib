<?php

// header("Access-Control-Allow-Origin: *");
if (!isset($_GET['start'])) die;
if (!isset($_GET['end'])) die;

$start = (int)$_GET['start'];
$end = (int)$_GET['end'];

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

try {
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_STRINGIFY_FETCHES);

	$table = "puzzles2";
	if (isset($_GET['dbphistomefel'])) $table = "phistomefel";

	$stmt = $conn->prepare("SELECT * FROM `" . $table . "` WHERE `id`>" . $start . " AND `id`<=" . $end);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($result);
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}
$conn = null;
