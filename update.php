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
		$fields = explode(":", $post->id, 2);

		$tableNumber = (int)$fields[0];
		$id = (int)$fields[1];

		$stmt = $stmts[$tableNumber];
		if (!$stmt) {
			$puzzleName = tableName($tableNumber);
			$sql = "UPDATE `$puzzleName` SET solveType=:solveType, 
			hiddenSimple=:hiddenSimple, omissionSimple=:omissionSimple, 
			naked2Simple=:naked2Simple, naked3Simple=:naked3Simple, nakedSimple=:nakedSimple, 
			omissionVisible=:omissionVisible, naked2Visible=:naked2Visible, nakedVisible=:nakedVisible, 
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
			'naked2Simple' => $post->naked2Simple,
			'naked3Simple' => $post->naked3Simple,
			'nakedSimple' => $post->nakedSimple,
			'omissionVisible' => $post->omissionVisible,
			'naked2Visible' => $post->naked2Visible,
			'nakedVisible' => $post->nakedVisible,
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
			'id' => $id
		]);
	}
} catch (PDOException $e) {
	// echo "Connection failed: " . $e->getMessage();
}
