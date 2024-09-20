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

	$stmt = $conn->prepare("SELECT * FROM `" . $table . "` WHERE `id`>" . $start . " AND `id`<=" . $end);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($result);
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}
$conn = null;
