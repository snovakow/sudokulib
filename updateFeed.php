<?php
if (!isset($_GET['start'])) die;
if (!isset($_GET['count'])) die;
$startNumber = (int)$_GET['start'];
$count = (int)$_GET['count'];

function tableName($number)
{
	$pad = str_pad($number, 3, "0", STR_PAD_LEFT);
	return "puzzles$pad";
}

const MAX_SIZE = 10000000;

try {
	$servername = "localhost";
	$username = "snovakow";
	$password = "kewbac-recge1-Fiwpux";
	$dbname = "sudoku";
	$db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

	$table = tableName((int)($startNumber / MAX_SIZE) + 1);
	$start = $startNumber % MAX_SIZE;
	$base = $startNumber - $start;
	$end = $start + $count;
	$stmt = $db->prepare("SELECT (`id` + $base) AS 'id', HEX(`puzzleData`) AS 'puzzleData' FROM `$table` WHERE `id`>$start AND `id`<=$end");
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($result);
} catch (PDOException $e) {
	// echo "Error: " . $e->getMessage();
}