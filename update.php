<?php
if (!isset($_GET['version'])) die();
$version = (int)$_GET['version'];
if ($version !== 2) die();

const MAX_SIZE = 10000000;

function tableName($number)
{
	$pad = str_pad($number, 3, "0", STR_PAD_LEFT);
	return "puzzles$pad";
}

$array = json_decode(file_get_contents("php://input"));

try {
	$servername = "localhost";
	$username = "snovakow";
	$password = "kewbac-recge1-Fiwpux";
	$dbname = "sudoku";
	$db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

	$stmts = [];
	foreach ($array as $post) {
		$id = (int)$post->id;
		$tableId = $id % MAX_SIZE;

		$tableNumber = (int)($id / MAX_SIZE) + 1;
		$stmt = $stmts[$tableNumber];
		if (!$stmt) {
			$puzzleName = tableName($tableNumber);
			$sql = "UPDATE `$puzzleName` SET solveType=:solveType, 
			hiddenSimple=:hiddenSimple, omissionSimple=:omissionSimple, nakedSimple=:nakedSimple, 
			nakedVisible=:nakedVisible, omissionVisible=:omissionVisible, 
			naked2=:naked2, naked3=:naked3, naked4=:naked4, 
			hidden1=:hidden1, hidden2=:hidden2, hidden3=:hidden3, hidden4=:hidden4, 
			omissions=:omissions, uniqueRectangle=:uniqueRectangle, yWing=:yWing, xyzWing=:xyzWing, 
			xWing=:xWing, swordfish=:swordfish, jellyfish=:jellyfish 
			WHERE id=:id";
			$stmt = $db->prepare($sql);
			$stmts[$tableNumber] = $stmt;
		}

		$stmt->execute([
			'solveType' => $post->solveType,
			'hiddenSimple' => $post->hiddenSimple,
			'omissionSimple' => $post->omissionSimple,
			'nakedSimple' => $post->nakedSimple,
			'nakedVisible' => $post->nakedVisible,
			'omissionVisible' => $post->omissionVisible,
			'naked2' => $post->naked2,
			'naked3' => $post->naked3,
			'naked4' => $post->naked4,
			'hidden1' => $post->hidden1,
			'hidden2' => $post->hidden2,
			'hidden3' => $post->hidden3,
			'hidden4' => $post->hidden4,
			'omissions' => $post->omissions,
			'uniqueRectangle' => $post->uniqueRectangle,
			'yWing' => $post->yWing,
			'xyzWing' => $post->xyzWing,
			'xWing' => $post->xWing,
			'swordfish' => $post->swordfish,
			'jellyfish' => $post->jellyfish,
			'id' => $tableId
		]);
	}
} catch (PDOException $e) {
	// echo "Connection failed: " . $e->getMessage();
}
