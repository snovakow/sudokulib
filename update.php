<?php
$version = 1;

if (!isset($_GET['version'])) die();
if ($_GET['version'] != $version) die();

$array = json_decode(file_get_contents("php://input"));

$table = "puzzles2";
if (isset($_GET['dbphistomefel'])) $table = "phistomefel";

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

try {
	$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$sql = "UPDATE `" . $table . "` SET simple=:simple, naked2=:naked2, naked3=:naked3, naked4=:naked4, 
	hidden2=:hidden2, hidden3=:hidden3, hidden4=:hidden4, omissions=:omissions,
	yWing=:yWing, xyzWing=:xyzWing, xWing=:xWing, swordfish=:swordfish, jellyfish=:jellyfish, 
	uniqueRectangle=:uniqueRectangle, phistomefel=:phistomefel, 
	has_naked2=:has_naked2, has_naked3=:has_naked3, has_naked4=:has_naked4, 
	has_hidden2=:has_hidden2, has_hidden3=:has_hidden3, has_hidden4=:has_hidden4, has_omissions=:has_omissions, 
	has_yWing=:has_yWing, has_xyzWing=:has_xyzWing, has_xWing=:has_xWing, has_swordfish=:has_swordfish, 
	has_jellyfish=:has_jellyfish, has_uniqueRectangle=:has_uniqueRectangle, has_phistomefel=:has_phistomefel, 
	superpositions=:superpositions, bruteForce=:bruteForce, solveType=:solveType 
	WHERE id=:id";

	$statement = $pdo->prepare($sql);

	foreach ($array as $post) {
		if (!isset($post->id)) continue;
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
		if (!isset($post->phistomefel)) continue;
		if (!isset($post->superpositions)) continue;
		if (!isset($post->bruteForce)) continue;

		$id = $post->id;
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
		$phistomefel = $post->phistomefel;
		$superpositions = $post->superpositions;
		$bruteForce = $post->bruteForce;

		$solveType = 1;
		if ($simple > 0) $solveType = 0;
		if ($bruteForce > 0) $solveType = 2;

		$has_naked2 = $naked2 > 0 ? 1 : 0;
		$has_naked3 = $naked3 > 0 ? 1 : 0;
		$has_naked4 = $naked4 > 0 ? 1 : 0;
		$has_hidden2 = $hidden2 > 0 ? 1 : 0;
		$has_hidden3 = $hidden3 > 0 ? 1 : 0;
		$has_hidden4 = $hidden4 > 0 ? 1 : 0;
		$has_omissions = $omissions > 0 ? 1 : 0;
		$has_yWing = $yWing > 0 ? 1 : 0;
		$has_xyzWing = $xyzWing > 0 ? 1 : 0;
		$has_xWing = $xWing > 0 ? 1 : 0;
		$has_swordfish = $swordfish > 0 ? 1 : 0;
		$has_jellyfish = $jellyfish > 0 ? 1 : 0;
		$has_uniqueRectangle = $uniqueRectangle > 0 ? 1 : 0;
		$has_phistomefel = $phistomefel > 0 ? 1 : 0;

		$statement->execute([
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
			'solveType' => $solveType,
			'id' => $id
		]);
	}
} catch (PDOException $e) {
	// echo "Connection failed: " . $e->getMessage();
}

$pdo = null;
