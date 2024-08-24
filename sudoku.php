<?php

// header("Access-Control-Allow-Origin: *");

if (!isset($_GET['strategy'])) die;
$type = $_GET['strategy'];

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

try {
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_STRINGIFY_FETCHES);

	$table = "puzzles";

	if ($type === "simple") {
		$stmt = $conn->prepare("
			SELECT p.`puzzleClues` FROM puzzles AS p WHERE p.`id` IN (
				SELECT s.`puzzle_id` FROM " . $type . " AS s   
					JOIN (
						SELECT FLOOR(RAND() * (SELECT MAX(`id`) FROM " . $type . ")) AS `rand_id`
					) r ON s.`id` > r.`rand_id`
			)
			LIMIT 1
		");
	} else {
		$stmt = $conn->prepare("
			SELECT `puzzleClues`, (s.`count`) FROM `puzzles` AS p 
			JOIN `" . $type . "` AS s
			ON s.`count` = (SELECT MAX(`count`) FROM " . $type . ") && s.`puzzle_id` = p.id
			ORDER BY RAND()
			LIMIT 1
		");
	}

	$stmt->execute();
	$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	foreach ($result as $clueCount => $row) $total += $row['count'];
	foreach ($result as $key => $row) {
		$puzzleClues = $row['puzzleClues'];
		echo $puzzleClues;
	}
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}
$conn = null;
