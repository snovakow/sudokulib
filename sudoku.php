<?php

// header("Access-Control-Allow-Origin: *");

if (!isset($_GET['strategy'])) die;
$type = $_GET['strategy'];

$strategy = false;

if ($type == 'simple') $strategy = $type;
if ($type == 'bruteForce') $strategy = $type;
if ($type == 'naked2') $strategy = $type;
if ($type == 'naked3') $strategy = $type;
if ($type == 'naked4') $strategy = $type;
if ($type == 'hidden2') $strategy = $type;
if ($type == 'hidden3') $strategy = $type;
if ($type == 'hidden4') $strategy = $type;
if ($type == 'omissions') $strategy = $type;
if ($type == 'uniqueRectangle') $strategy = $type;
if ($type == 'yWing') $strategy = $type;
if ($type == 'xyzWing') $strategy = $type;
if ($type == 'xWing') $strategy = $type;
if ($type == 'swordfish') $strategy = $type;
if ($type == 'jellyfish') $strategy = $type;
if ($type == 'custom') $strategy = $type;

if (!$strategy) die();

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

try {
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	// $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_STRINGIFY_FETCHES);

	if ($type == 'custom') {
		$stmt = $conn->prepare("SELECT `id`, `title`, `puzzle` FROM `hardcoded`");
		$stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		$results = array();
		foreach ($result as $key => $row) {
			$id = $row['id'];
			$title = $row['title'];
			$puzzle = $row['puzzle'];
			$results[] = $id . "," . $title . "," . $puzzle;
		}
		echo implode(":", $results);
		die;
	}

	$stmt = $conn->prepare("
		SELECT s.`puzzle_id`, s.`table` FROM `" . $strategy . "` AS s   
			JOIN (
				SELECT FLOOR(RAND() * (SELECT MAX(`id`) FROM `" . $strategy . "`)) AS `rand_id`
			) r ON s.`id` > r.`rand_id`
		LIMIT 1
	");
	$stmt->execute();
	$result = $stmt->fetch();

	$stmt = $conn->prepare(
		"SELECT `id`, `puzzleClues`, `puzzleFilled` FROM `" . $result['table'] . "` WHERE `id`=" . $result['puzzle_id']
	);

	$stmt->execute();
	$result = $stmt->fetch();
	$id = $result['id'];
	$puzzleClues = $result['puzzleClues'];
	$puzzleFilled = $result['puzzleFilled'];
	echo $id . ":" . $puzzleClues . ":" . $puzzleFilled;
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}
$conn = null;
