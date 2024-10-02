<?php

function flushOut($message)
{
	echo $message . "<br/>";
}

function percentage($count, $total)
{
	$precision = 100000;
	$number = $count / $total;
	$formatted = ceil(100 * $number * $precision) / $precision;
	return rtrim(rtrim(sprintf('%f', $formatted), '0'), ".") . "%";
}
function getStat($title, $count, $total)
{
	return $title . ": " . percentage($count, $total) . " " . number_format($count);
}
function printStat($title, $count, $total)
{
	echo getStat($title, $count, $total) . "<br/>";
}

function queryStrategy($conn, $table)
{
	$stmt = $conn->prepare("SELECT COUNT(*) as count, MAX(count) as max FROM `" . $table . "`");
	$stmt->execute();
	$result = $stmt->fetch();
	return $result;
}

// header("Access-Control-Allow-Origin: *");

if (!isset($_GET['mode'])) die;

// 0 = Count
// 1 = Strategies
// 2 = Clues
$mode = 0;

$mode = (int)$_GET['mode'];
if ($mode !== 0 && $mode !== 1 && $mode !== 2) die;

if (!isset($_GET['table'])) {
	if ($mode === 0 || $mode === 2) die;
}

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

try {
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	// $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_STRINGIFY_FETCHES);

	if ($mode === 1) {
		flushOut("--- Strategies");

		$naked2 = queryStrategy($conn, 'naked2');
		$naked3 = queryStrategy($conn, 'naked3');
		$naked4 = queryStrategy($conn, 'naked4');
		$hidden2 = queryStrategy($conn, 'hidden2');
		$hidden3 = queryStrategy($conn, 'hidden3');
		$hidden4 = queryStrategy($conn, 'hidden4');
		$omissions = queryStrategy($conn, 'omissions');
		$yWing = queryStrategy($conn, 'yWing');
		$xyzWing = queryStrategy($conn, 'xyzWing');
		$xWing = queryStrategy($conn, 'xWing');
		$swordfish = queryStrategy($conn, 'swordfish');
		$jellyfish = queryStrategy($conn, 'jellyfish');
		$uniqueRectangle = queryStrategy($conn, 'uniqueRectangle');

		$candidates = 0;
		$candidates += $naked2['count'];
		$candidates += $naked3['count'];
		$candidates += $naked4['count'];
		$candidates += $hidden2['count'];
		$candidates += $hidden3['count'];
		$candidates += $hidden4['count'];
		$candidates += $omissions['count'];
		$candidates += $yWing['count'];
		$candidates += $xyzWing['count'];
		$candidates += $xWing['count'];
		$candidates += $swordfish['count'];
		$candidates += $jellyfish['count'];
		$candidates += $uniqueRectangle['count'];

		if ($candidates > 0) {
			printStat("Naked 2 (" . $naked2['max'] . ")", $naked2['count'], $candidates);
			printStat("Naked 3 (" . $naked3['max'] . ")", $naked3['count'], $candidates);
			printStat("Naked 4 (" . $naked4['max'] . ")", $naked4['count'], $candidates);
			printStat("Hidden 2 (" . $hidden2['max'] . ")", $hidden2['count'], $candidates);
			printStat("Hidden 3 (" . $hidden3['max'] . ")", $hidden3['count'], $candidates);
			printStat("Hidden 4 (" . $hidden4['max'] . ")", $hidden4['count'], $candidates);
			printStat("Omissions (" . $omissions['max'] . ")", $omissions['count'], $candidates);
			printStat("yWing (" . $yWing['max'] . ")", $yWing['count'], $candidates);
			printStat("xyzWing (" . $xyzWing['max'] . ")", $xyzWing['count'], $candidates);
			printStat("xWing (" . $xWing['max'] . ")", $xWing['count'], $candidates);
			printStat("swordfish (" . $swordfish['max'] . ")", $swordfish['count'], $candidates);
			printStat("jellyfish (" . $jellyfish['max'] . ")", $jellyfish['count'], $candidates);
			printStat("uniqueRectangle (" . $uniqueRectangle['max'] . ")", $uniqueRectangle['count'], $candidates);
		}
		echo  "<br/>";
		die;
	}

	$tables = explode(",", $_GET['table']);

	$total = 0;
	$totals = array();
	foreach ($tables as $table) {
		$stmt = $conn->prepare("
				SELECT MAX(id) as totalPuzzles FROM `" . $table . "`
			");
		$stmt->execute();
		$totalPuzzles = $stmt->fetch()["totalPuzzles"];
		$total += $totalPuzzles;
		$totals[] =  $table . "=" . $totalPuzzles;
		if ($mode !== 0) flushOut($table . ": " . number_format($totalPuzzles));
	}
	if ($mode === 0) {
		echo implode(",", $totals);
	} else {
		if (count($tables) > 1) flushOut("Total Puzzles: " . number_format($total));
		echo  "<br/>";
	}

	if ($mode === 2) {
		flushOut("--- Clues");
		$counts = array();
		$count0 = array();
		$count1 = array();
		$count2 = array();
		foreach ($tables as $table) {
			$stmt = $conn->prepare("SELECT `clueCount`, `solveType`, COUNT(*) as count FROM `" . $table . "` GROUP BY `clueCount`, `solveType`");
			$stmt->execute();
			$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			foreach ($result as $key => $row) {
				$clueCount = $row['clueCount'];
				$solveType = $row['solveType'];
				$count = $row['count'];

				if (!$counts[$clueCount]) $counts[$clueCount] = 0;
				if (!$count0[$clueCount]) $count0[$clueCount] = 0;
				if (!$count1[$clueCount]) $count1[$clueCount] = 0;
				if (!$count2[$clueCount]) $count2[$clueCount] = 0;

				$counts[$clueCount] += $count;
				if ($solveType == 0) $count0[$clueCount] += $count;
				if ($solveType == 1) $count1[$clueCount] += $count;
				if ($solveType == 2) $count2[$clueCount] += $count;
			}
		}

		foreach ($counts as $clueCount => $count) printStat($clueCount, $count, $total);

		$counts0 = 0;
		$counts1 = 0;
		$counts2 = 0;
		foreach ($count0 as $clueCount => $count) $counts0 += $count;
		foreach ($count1 as $clueCount => $count) $counts1 += $count;
		foreach ($count2 as $clueCount => $count) $counts2 += $count;
		printStat("Simples", $counts0, $total);
		foreach ($counts as $clueCount => $count) printStat($clueCount, $count0[$clueCount], $count);

		printStat("Strategies", $counts1, $total);
		foreach ($counts as $clueCount => $count) printStat($clueCount, $count1[$clueCount], $count);

		printStat("Brute Force", $counts2, $total);
		foreach ($counts as $clueCount => $count) printStat($clueCount, $count2[$clueCount], $count);

		echo  "<br/>";
	}
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}
$conn = null;
