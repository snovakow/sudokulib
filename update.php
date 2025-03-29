<?php
if (!isset($_GET['version'])) die();
$version = (int)$_GET['version'];
if ($version !== 2) die();

const MAX_SIZE = 10000000;

function tableName($number)
{
	$pad = str_pad($number, 3, "0", STR_PAD_LEFT);
	$puzzles = "puzzles";
	return "$puzzles$pad";
}

function insertValues($number, $values)
{
	$valueList = implode(",", $values);
	$table = tableName($number);
	return "INSERT INTO `$table` (id, solveType, 
		hiddenSimple, nakedSimple, omissionSimple, 
		omissionVisible, nakedVisible, 
		naked2, naked3, naked4, hidden1, hidden2, hidden3, hidden4, omissions, 
		uniqueRectangle, yWing, xyzWing, xWing, swordfish, jellyfish) VALUES $valueList ON DUPLICATE KEY UPDATE 
        solveType = VALUES(solveType), 
        hiddenSimple = VALUES(hiddenSimple), 
        nakedSimple = VALUES(nakedSimple), 
        omissionSimple = VALUES(omissionSimple), 
        omissionVisible = VALUES(omissionVisible), 
        nakedVisible = VALUES(nakedVisible), 
        naked2 = VALUES(naked2), 
        naked3 = VALUES(naked3), 
        naked4 = VALUES(naked4), 
        hidden1 = VALUES(hidden1), 
        hidden2 = VALUES(hidden2), 
        hidden3 = VALUES(hidden3), 
        hidden4 = VALUES(hidden4), 
        omissions = VALUES(omissions), 
        uniqueRectangle = VALUES(uniqueRectangle), 
        yWing = VALUES(yWing), 
        xyzWing = VALUES(xyzWing), 
        xWing = VALUES(xWing), 
        swordfish = VALUES(swordfish), 
        jellyfish = VALUES(jellyfish)";
}

try {
	$servername = "localhost";
	$username = "snovakow";
	$password = "kewbac-recge1-Fiwpux";
	$dbname = "sudoku";
	$db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

	$array = json_decode(file_get_contents("php://input"));

	$valueLists = [];
	foreach ($array as $post) {
		$fields = explode(":", $post->id, 2);

		$tableNumber = (int)$fields[0];
		$id = (int)$fields[1];

		$valueList = [
			$id,
			$post->solveType,
			$post->hiddenSimple,
			$post->nakedSimple,
			$post->omissionSimple,
			$post->omissionVisible,
			$post->nakedVisible,
			$post->naked2,
			$post->naked3,
			$post->naked4,
			$post->hidden1,
			$post->hidden2,
			$post->hidden3,
			$post->hidden4,
			$post->omissions,
			$post->uniqueRectangle,
			$post->yWing,
			$post->xyzWing,
			$post->xWing,
			$post->swordfish,
			$post->jellyfish,
		];
		$flatList = implode(',', $valueList);

		if ($valueLists[$tableNumber]) $valueLists[$tableNumber][] = "($flatList)";
		else $valueLists[$tableNumber] = ["($flatList)"];
	}
	foreach ($valueLists as $tableNumber => $values) {
		$db->exec(insertValues($tableNumber, $values));
	}
} catch (PDOException $e) {
	// echo "Connection failed: " . $e->getMessage();
}
