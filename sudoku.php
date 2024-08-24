<?php

// if(!isset($_GET['id'])) die();

// header("Access-Control-Allow-Origin: *");

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

try {
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_STRINGIFY_FETCHES);

	$type = "simple";
	if (isset($_GET['strategy'])) $type = $_GET['strategy'];

	$table = "puzzles";
	$stmt = $conn->prepare("
		SELECT p.`puzzleClues` FROM puzzles AS p WHERE p.`id` IN (
			SELECT s.`puzzle_id` FROM " . $type . " AS s   
				JOIN (
					SELECT FLOOR(RAND() * (SELECT MAX(`id`) FROM " . $type . ")) AS `rand_id`
				) r ON s.`id` > r.`rand_id`
		)
		LIMIT 1
	");

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

/*
simple naked2 naked3 naked4 hidden2 hidden3 hidden4 yWing xyzWing xWing swordfish jellyfish uniqueRectangle
has_naked2 has_naked3 has_naked4 has_hidden2 has_hidden3 has_hidden4 has_yWing has_xyzWing has_xWing has_swordfish has_jellyfish has_uniqueRectangle

INSERT INTO `simple` (`puzzle_id`)
SELECT `id`
FROM `puzzles` AS p
WHERE p.`simple` > 0

INSERT INTO `naked2` (`puzzle_id`, `count`)
SELECT `id`, `naked2`
FROM `puzzles` AS p
WHERE p.naked2>0 AND p.naked3=0 AND p.naked4=0 AND p.hidden2=0 AND p.hidden3=0 AND p.hidden4=0 AND p.yWing=0 AND p.xyzWing=0 AND p.xWing=0 AND p.swordfish=0 AND p.jellyfish=0 AND p.uniqueRectangle=0 AND p.bruteForce=0
*/