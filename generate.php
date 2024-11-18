<?php
$version = 1;
if (!isset($_GET['version'])) die();
if ($_GET['version'] != $version) die();

if (!isset($_GET['table'])) die();
$table = $_GET['table'];

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

$array = json_decode(file_get_contents("php://input"));

try {
	$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	// $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$pdo->exec("LOCK TABLES `" . $table . "` WRITE");

	$sql = "SELECT MAX(id) as totalPuzzles FROM `" . $table . "`";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$result = $stmt->fetch();
	$count = $result['totalPuzzles'];

	if ($count === NULL) {
		$stmt = $pdo->prepare("
			SELECT table_name FROM information_schema.tables WHERE table_schema = 'sudoku' AND table_name = '" . $table . "' LIMIT 1;
		");
		$stmt->execute();
		if ($stmt->fetch()["table_name"] === NULL) exit("-1:0");
		$count = 0;
	}

	$maxSize = 10000000;
	if ($count >= $maxSize) exit("0:0");

	$sql = "INSERT INTO `" . $table . "` (puzzleData, clueCount, 
			simple, naked2, naked3, naked4, hidden2, hidden3, hidden4, omissions, 
			yWing, xyzWing, xWing, swordfish, jellyfish, uniqueRectangle, 
			has_naked2, has_naked3, has_naked4, has_hidden2, has_hidden3, has_hidden4, has_omissions, 
			has_yWing, has_xyzWing, has_xWing, has_swordfish, has_jellyfish, has_uniqueRectangle, 
			bruteForce, solveType) VALUES ";

	$inserted = 0;
	$values = array();
	foreach ($array as $post) {
		if (!isset($post->puzzleData)) continue;
		if (!isset($post->clueCount)) continue;
		if (!isset($post->simple)) continue;
		if (!isset($post->naked2)) continue;
		if (!isset($post->naked3)) continue;
		if (!isset($post->naked4)) continue;
		if (!isset($post->hidden2)) continue;
		if (!isset($post->hidden3)) continue;
		if (!isset($post->hidden4)) continue;
		if (!isset($post->omissions)) continue;
		if (!isset($post->yWing)) continue;
		if (!isset($post->xyzWing)) continue;
		if (!isset($post->xWing)) continue;
		if (!isset($post->swordfish)) continue;
		if (!isset($post->jellyfish)) continue;
		if (!isset($post->uniqueRectangle)) continue;
		if (!isset($post->bruteForce)) continue;

		if (!isset($post->has_naked2)) continue;
		if (!isset($post->has_naked3)) continue;
		if (!isset($post->has_naked4)) continue;
		if (!isset($post->has_hidden2)) continue;
		if (!isset($post->has_hidden3)) continue;
		if (!isset($post->has_hidden4)) continue;
		if (!isset($post->has_omissions)) continue;
		if (!isset($post->has_uniqueRectangle)) continue;
		if (!isset($post->has_yWing)) continue;
		if (!isset($post->has_xyzWing)) continue;
		if (!isset($post->has_xWing)) continue;
		if (!isset($post->has_swordfish)) continue;
		if (!isset($post->has_jellyfish)) continue;

		$puzzleData = $post->puzzleData;
		$clueCount = $post->clueCount;
		$simple = $post->simple;
		$naked2 = $post->naked2;
		$naked3 = $post->naked3;
		$naked4 = $post->naked4;
		$hidden2 = $post->hidden2;
		$hidden3 = $post->hidden3;
		$hidden4 = $post->hidden4;
		$omissions = $post->omissions;
		$yWing = $post->yWing;
		$xyzWing = $post->xyzWing;
		$xWing = $post->xWing;
		$swordfish = $post->swordfish;
		$jellyfish = $post->jellyfish;
		$uniqueRectangle = $post->uniqueRectangle;
		$bruteForce = $post->bruteForce;

		$solveType = 1;
		if ($simple > 0) $solveType = 0;
		if ($bruteForce > 0) $solveType = 2;

		$has_naked2 = $post->has_naked2;
		$has_naked3 = $post->has_naked3;
		$has_naked4 = $post->has_naked4;
		$has_hidden2 = $post->has_hidden2;
		$has_hidden3 = $post->has_hidden3;
		$has_hidden4 = $post->has_hidden4;
		$has_omissions = $post->has_omissions;
		$has_uniqueRectangle = $post->has_uniqueRectangle;
		$has_yWing = $post->has_yWing;
		$has_xyzWing = $post->has_xyzWing;
		$has_xWing = $post->has_xWing;
		$has_swordfish = $post->has_swordfish;
		$has_jellyfish = $post->has_jellyfish;

		$valueList = array(
			$clueCount,
			$simple,
			$naked2,
			$naked3,
			$naked4,
			$hidden2,
			$hidden3,
			$hidden4,
			$omissions,
			$yWing,
			$xyzWing,
			$xWing,
			$swordfish,
			$jellyfish,
			$uniqueRectangle,
			$has_naked2,
			$has_naked3,
			$has_naked4,
			$has_hidden2,
			$has_hidden3,
			$has_hidden4,
			$has_omissions,
			$has_yWing,
			$has_xyzWing,
			$has_xWing,
			$has_swordfish,
			$has_jellyfish,
			$has_uniqueRectangle,
			$bruteForce,
			$solveType
		);
		$values[] = "(X'" . $puzzleData . "'," . implode(",", $valueList) . ")";

		$count++;
		$inserted++;
		if ($count >= $maxSize) break;
	}

	$sql .= implode(",", $values);
	$pdo->exec($sql);

	$pdo->exec('UNLOCK TABLES');

	echo ($count . ":" . $inserted);
} catch (PDOException $e) {
	// echo "Connection failed: " . $e->getMessage();
}

$pdo = null;
