<?php
if (!isset($_GET['version'])) die();
$version = 1;
if ($_GET['version'] != $version) die();

if (!isset($_GET['puzzleClues'])) die();
if (!isset($_GET['puzzleFilled'])) die();
if (!isset($_GET['clueCount'])) die();
if (!isset($_GET['simple'])) die();
if (!isset($_GET['naked2'])) die();
if (!isset($_GET['naked3'])) die();
if (!isset($_GET['naked4'])) die();
if (!isset($_GET['hidden2'])) die();
if (!isset($_GET['hidden3'])) die();
if (!isset($_GET['hidden4'])) die();
if (!isset($_GET['omissions'])) die();
if (!isset($_GET['yWing'])) die();
if (!isset($_GET['xyzWing'])) die();
if (!isset($_GET['xWing'])) die();
if (!isset($_GET['swordfish'])) die();
if (!isset($_GET['jellyfish'])) die();
if (!isset($_GET['uniqueRectangle'])) die();
if (!isset($_GET['bruteForce'])) die();

if (!isset($_GET['has_naked2'])) die();
if (!isset($_GET['has_naked3'])) die();
if (!isset($_GET['has_naked4'])) die();
if (!isset($_GET['has_hidden2'])) die();
if (!isset($_GET['has_hidden3'])) die();
if (!isset($_GET['has_hidden4'])) die();
if (!isset($_GET['has_omissions'])) die();
if (!isset($_GET['has_uniqueRectangle'])) die();
if (!isset($_GET['has_yWing'])) die();
if (!isset($_GET['has_xyzWing'])) die();
if (!isset($_GET['has_xWing'])) die();
if (!isset($_GET['has_swordfish'])) die();
if (!isset($_GET['has_jellyfish'])) die();

if (!isset($_GET['table'])) die();
$table = $_GET['table'];

$puzzleClues = $_GET['puzzleClues'];
$puzzleFilled = $_GET['puzzleFilled'];
$clueCount = $_GET['clueCount'];
$simple = $_GET['simple'];
$naked2 = $_GET['naked2'];
$naked3 = $_GET['naked3'];
$naked4 = $_GET['naked4'];
$hidden2 = $_GET['hidden2'];
$hidden3 = $_GET['hidden3'];
$hidden4 = $_GET['hidden4'];
$omissions = $_GET['omissions'];
$yWing = $_GET['yWing'];
$xyzWing = $_GET['xyzWing'];
$xWing = $_GET['xWing'];
$swordfish = $_GET['swordfish'];
$jellyfish = $_GET['jellyfish'];
$uniqueRectangle = $_GET['uniqueRectangle'];
$bruteForce = $_GET['bruteForce'];

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

$solveType = 1;
if ($simple > 0) $solveType = 0;
if ($bruteForce > 0) $solveType = 2;

$has_naked2 = $_GET['has_naked2'];
$has_naked3 = $_GET['has_naked3'];
$has_naked4 = $_GET['has_naked4'];
$has_hidden2 = $_GET['has_hidden2'];
$has_hidden3 = $_GET['has_hidden3'];
$has_hidden4 = $_GET['has_hidden4'];
$has_omissions = $_GET['has_omissions'];
$has_uniqueRectangle = $_GET['has_uniqueRectangle'];
$has_yWing = $_GET['has_yWing'];
$has_xyzWing = $_GET['has_xyzWing'];
$has_xWing = $_GET['has_xWing'];
$has_swordfish = $_GET['has_swordfish'];
$has_jellyfish = $_GET['has_jellyfish'];

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
		if ($stmt->fetch()["table_name"] === NULL) exit("-1");
		$count = 0;
	}

	if ($count >= 10000000) {
		exit("0");
	}

	$sql = "INSERT INTO `" . $table . "` (puzzleClues, puzzleFilled, clueCount, 
			simple, naked2, naked3, naked4, hidden2, hidden3, hidden4, omissions, 
			yWing, xyzWing, xWing, swordfish, jellyfish, uniqueRectangle, 
			has_naked2, has_naked3, has_naked4, has_hidden2, has_hidden3, has_hidden4, has_omissions, 
			has_yWing, has_xyzWing, has_xWing, has_swordfish, has_jellyfish, has_uniqueRectangle, 
			bruteForce, solveType) 
			VALUES (X'" . $puzzleClues . "', X'" . $puzzleFilled . "', :clueCount, :simple, 
			:naked2, :naked3, :naked4, :hidden2, :hidden3, :hidden4, :omissions, 
			:yWing, :xyzWing, :xWing, :swordfish, :jellyfish, :uniqueRectangle, 
			:has_naked2, :has_naked3, :has_naked4, :has_hidden2, :has_hidden3, :has_hidden4, :has_omissions, 
			:has_yWing, :has_xyzWing, :has_xWing, :has_swordfish, :has_jellyfish, :has_uniqueRectangle, 
			:bruteForce, :solveType)";
	$stmt = $pdo->prepare($sql);

	$stmt->bindValue('clueCount', $clueCount, PDO::PARAM_INT);
	$stmt->bindValue('simple', $simple, PDO::PARAM_INT);
	$stmt->bindValue('naked2', $naked2, PDO::PARAM_INT);
	$stmt->bindValue('naked3', $naked3, PDO::PARAM_INT);
	$stmt->bindValue('naked4', $naked4, PDO::PARAM_INT);
	$stmt->bindValue('hidden2', $hidden2, PDO::PARAM_INT);
	$stmt->bindValue('hidden3', $hidden3, PDO::PARAM_INT);
	$stmt->bindValue('hidden4', $hidden4, PDO::PARAM_INT);
	$stmt->bindValue('omissions', $omissions, PDO::PARAM_INT);
	$stmt->bindValue('yWing', $yWing, PDO::PARAM_INT);
	$stmt->bindValue('xyzWing', $xyzWing, PDO::PARAM_INT);
	$stmt->bindValue('xWing', $xWing, PDO::PARAM_INT);
	$stmt->bindValue('swordfish', $swordfish, PDO::PARAM_INT);
	$stmt->bindValue('jellyfish', $jellyfish, PDO::PARAM_INT);
	$stmt->bindValue('uniqueRectangle', $uniqueRectangle, PDO::PARAM_INT);
	$stmt->bindValue('has_naked2', $has_naked2, PDO::PARAM_INT);
	$stmt->bindValue('has_naked3', $has_naked3, PDO::PARAM_INT);
	$stmt->bindValue('has_naked4', $has_naked4, PDO::PARAM_INT);
	$stmt->bindValue('has_hidden2', $has_hidden2, PDO::PARAM_INT);
	$stmt->bindValue('has_hidden3', $has_hidden3, PDO::PARAM_INT);
	$stmt->bindValue('has_hidden4', $has_hidden4, PDO::PARAM_INT);
	$stmt->bindValue('has_omissions', $has_omissions, PDO::PARAM_INT);
	$stmt->bindValue('has_yWing', $has_yWing, PDO::PARAM_INT);
	$stmt->bindValue('has_xyzWing', $has_xyzWing, PDO::PARAM_INT);
	$stmt->bindValue('has_xWing', $has_xWing, PDO::PARAM_INT);
	$stmt->bindValue('has_swordfish', $has_swordfish, PDO::PARAM_INT);
	$stmt->bindValue('has_jellyfish', $has_jellyfish, PDO::PARAM_INT);
	$stmt->bindValue('has_uniqueRectangle', $has_uniqueRectangle, PDO::PARAM_INT);
	$stmt->bindValue('bruteForce', $bruteForce, PDO::PARAM_INT);
	$stmt->bindValue('solveType', $solveType, PDO::PARAM_INT);

	$stmt->execute();

	$pdo->exec('UNLOCK TABLES');

	echo ($count + 1);
} catch (PDOException $e) {
	// echo "Connection failed: " . $e->getMessage();
}

$pdo = null;
