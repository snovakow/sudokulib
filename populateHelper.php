<?php
die();

$table = "puzzles";

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

try {
	$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$strategies = array("simple", "naked2", "naked3", "naked4", "hidden2", "hidden3", "hidden4", "omissions", "yWing", "xyzWing", "xWing", "swordfish", "jellyfish", "uniqueRectangle");

	foreach ($strategies as $strategy) {
		$sql = "INSERT INTO " . $table . " (puzzleClues, puzzleFilled, clueCount, simple, naked2, naked3, naked4, hidden2, hidden3, hidden4, omissions, 
		yWing, xyzWing, xWing, swordfish, jellyfish, uniqueRectangle, phistomefel, has_naked2, has_naked3, has_naked4, has_hidden2, has_hidden3, has_hidden4, has_omissions, 
		has_yWing, has_xyzWing, has_xWing, has_swordfish, has_jellyfish, has_uniqueRectangle, has_phistomefel, superpositions, bruteForce, solveType) 
		VALUES (:puzzleClues, :puzzleFilled, :clueCount, :simple, :naked2, :naked3, :naked4, :hidden2, :hidden3, :hidden4, :omissions, 
		:yWing, :xyzWing, :xWing, :swordfish, :jellyfish, :uniqueRectangle, :phistomefel, :has_naked2, :has_naked3, :has_naked4, :has_hidden2, :has_hidden3, :has_hidden4, :has_omissions, 
		:has_yWing, :has_xyzWing, :has_xWing, :has_swordfish, :has_jellyfish, :has_uniqueRectangle, :has_phistomefel, :superpositions, :bruteForce, :solveType)";

		$statement = $pdo->prepare($sql);

		$statement->execute([
			'puzzleClues' => $puzzleClues,
			'puzzleFilled' => $puzzleFilled,
			'clueCount' => $clueCount,
			'simple' => $simple,
			'naked2' => $naked2,
			'naked3' => $naked3,
			'naked4' => $naked4,
			'hidden2' => $hidden2,
			'hidden3' => $hidden3,
			'hidden4' => $hidden4,
			'omissions' => $omissions,
			'yWing' => $yWing,
			'xyzWing' => $xyzWing,
			'xWing' => $xWing,
			'swordfish' => $swordfish,
			'jellyfish' => $jellyfish,
			'uniqueRectangle' => $uniqueRectangle,
			'phistomefel' => $phistomefel,
			'has_naked2' => $has_naked2,
			'has_naked3' => $has_naked3,
			'has_naked4' => $has_naked4,
			'has_hidden2' => $has_hidden2,
			'has_hidden3' => $has_hidden3,
			'has_hidden4' => $has_hidden4,
			'has_omissions' => $has_omissions,
			'has_yWing' => $has_yWing,
			'has_xyzWing' => $has_xyzWing,
			'has_xWing' => $has_xWing,
			'has_swordfish' => $has_swordfish,
			'has_jellyfish' => $has_jellyfish,
			'has_uniqueRectangle' => $has_uniqueRectangle,
			'has_phistomefel' => $has_phistomefel,
			'superpositions' => $superpositions,
			'bruteForce' => $bruteForce,
			'solveType' => $solveType
		]);
	}
} catch (PDOException $e) {
	// echo "Connection failed: " . $e->getMessage();
}

$pdo = null;

/*
TRUNCATE TABLE simple

simple naked2 naked3 naked4 hidden2 hidden3 hidden4 omissions yWing xyzWing xWing swordfish jellyfish uniqueRectangle
has_naked2 has_naked3 has_naked4 has_hidden2 has_hidden3 has_hidden4 has_omissions has_yWing has_xyzWing has_xWing has_swordfish has_jellyfish has_uniqueRectangle

INSERT INTO `simple` (`puzzle_id`)
SELECT `id`
FROM `puzzles` AS p
WHERE p.`simple` > 0

INSERT INTO `naked2` (`puzzle_id`, `count`)
SELECT `id`, `naked2`
FROM `puzzles` AS p
WHERE p.`naked2`>0 AND p.`naked3`=0 AND p.`naked4`=0 AND p.`hidden2`=0 AND p.`hidden3`=0 AND p.`hidden4`=0 AND p.`omissions`=0 AND p.`yWing`=0 AND p.`xyzWing`=0 AND p.`xWing`=0 AND p.`swordfish`=0 AND p.`jellyfish`=0 AND p.`uniqueRectangle`=0 AND p.`bruteForce`=0

INSERT INTO `phistomefelRing` (`puzzle_id`, `count`)
SELECT `id`, `phistomefel`
FROM `phistomefel` AS p
WHERE p.`naked2`=0 AND p.`naked3`=0 AND p.`naked4`=0 AND p.`hidden2`=0 AND p.`hidden3`=0 AND p.`hidden4`=0 AND p.`omissions`=0 AND p.`yWing`=0 AND p.`xyzWing`=0 AND p.`xWing`=0 AND p.`swordfish`=0 AND p.`jellyfish`=0 AND p.`uniqueRectangle`=0 AND p.`phistomefel`>0 AND p.`bruteForce`=0
*/
