<?php

function flushOut($message)
{
	echo $message . "<br/>";
	// ob_flush();
	// flush();
}

function percentage($count, $total)
{
	$precision = 100000;
	$number = $count / $total;
	$formatted = ceil(100 * $number * $precision) / $precision;
	return rtrim(rtrim(sprintf('%f', $formatted), '0'), ".") . "%";
}
function printStat($title, $count, $total)
{
	echo $title . ": " . percentage($count, $total) . " " . number_format($count) . "<br/>";
}

// if(!isset($_GET['id'])) die();

// header("Access-Control-Allow-Origin: *");

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

try {
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_STRINGIFY_FETCHES);

	$total = 0;
	$counts = array();

	flushOut("--- Clues");

	$table = "puzzles";
	if (isset($_GET['dbphistomefel'])) $table = "phistomefel";

	$stmt = $conn->prepare("SELECT `clueCount`, COUNT(*) as count FROM " . $table . " GROUP BY `clueCount`");
	$stmt->execute();
	$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	foreach ($result as $clueCount => $row) $total += $row['count'];
	foreach ($result as $key => $row) {
		$clueCount = $row['clueCount'];
		$count = $row['count'];

		printStat($clueCount, $count, $total);
	}
	echo  "<br/>";

	flushOut("--- Strategies");

	$stmt = $conn->prepare("
		SELECT
		SUM(`has_naked2`) AS naked2,
		SUM(`has_naked3`) AS naked3,
		SUM(`has_naked4`) AS naked4,
		SUM(`has_hidden2`) AS hidden2,
		SUM(`has_hidden3`) AS hidden3,
		SUM(`has_hidden4`) AS hidden4,
		SUM(`has_yWing`) AS yWing,
		SUM(`has_xyzWing`) AS xyzWing,
		SUM(`has_xWing`) AS xWing,
		SUM(`has_swordfish`) AS swordfish,
		SUM(`has_jellyfish`) AS jellyfish,
		SUM(`has_uniqueRectangle`) AS uniqueRectangle,
		SUM(`has_phistomefel`) AS phistomefel
		FROM " . $table . " WHERE `bruteForce` = 0");
	$stmt->execute();
	$solveTypes = $stmt->fetch();

	$naked2 = $solveTypes['naked2'];
	$naked3 = $solveTypes['naked3'];
	$naked4 = $solveTypes['naked4'];
	$hidden2 = $solveTypes['hidden2'];
	$hidden3 = $solveTypes['hidden3'];
	$hidden4 = $solveTypes['hidden4'];
	$yWing = $solveTypes['yWing'];
	$xyzWing = $solveTypes['xyzWing'];
	$xWing = $solveTypes['xWing'];
	$swordfish = $solveTypes['swordfish'];
	$jellyfish = $solveTypes['jellyfish'];
	$uniqueRectangle = $solveTypes['uniqueRectangle'];
	$phistomefel = $solveTypes['phistomefel'];

	$markers = 0;
	$markers += $naked2;
	$markers += $naked3;
	$markers += $naked4;
	$markers += $hidden2;
	$markers += $hidden3;
	$markers += $hidden4;
	$markers += $yWing;
	$markers += $xyzWing;
	$markers += $xWing;
	$markers += $swordfish;
	$markers += $jellyfish;
	$markers += $uniqueRectangle;
	$markers += $phistomefel;

	printStat("Naked 2", $naked2, $markers);
	printStat("Naked 3", $naked3, $markers);
	printStat("Naked 4", $naked4, $markers);
	printStat("Hidden 2", $hidden2, $markers);
	printStat("Hidden 3", $hidden3, $markers);
	printStat("Hidden 4", $hidden4, $markers);
	printStat("yWing", $yWing, $markers);
	printStat("xyzWing", $xyzWing, $markers);
	printStat("xWing", $xWing, $markers);
	printStat("swordfish", $swordfish, $markers);
	printStat("jellyfish", $jellyfish, $markers);
	printStat("uniqueRectangle", $uniqueRectangle, $markers);
	printStat("phistomefel", $phistomefel, $markers);
	echo  "<br/>";

	flushOut("--- Stats");

	$stmt = $conn->prepare("SELECT `solveType`, COUNT(*) as count FROM " . $table . " GROUP BY `solveType`");
	$stmt->execute();
	$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	foreach ($result as $key => $row) {
		$solveType = $row['solveType'];
		$count = $row['count'];

		$type = "Simples";
		if ($solveType === "1") $type =  "Markers";
		else if ($solveType === "2") $type = "Brute Force";
		printStat($type, $count, $total);
	}
	echo  "Total Puzzles: " . number_format($total) . "<br/>";
	echo  "<br/>";
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}
$conn = null;
