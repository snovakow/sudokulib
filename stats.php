<?php

const MAX_SIZE = 10000000;

function totalCount($tableCount, $puzzleCount)
{
	if ($tableCount === 0) return 0;
	return (($tableCount - 1) * MAX_SIZE) +  $puzzleCount;
}

function tableName($number)
{
	$pad = str_pad($number, 3, "0", STR_PAD_LEFT);
	return "puzzles$pad";
}

function flushOut($message)
{
	echo "$message<br/>";
}

function percentage($count, $total, $precision)
{
	$percent = number_format(100.0 * $count / $total, $precision, '.', "");
	$pad = str_pad($percent, $precision + 3, "0", STR_PAD_LEFT);
	return "$pad%";
}

function getStat($title, $count, $total, $precision)
{
	$percent = percentage($count, $total, $precision);
	$number = number_format($count);
	return "$title: $percent $number";
}

function printStat($title, $count, $total, $precision)
{
	$stat = getStat($title, $count, $total, $precision);
	echo "$stat<br/>";
}

function queryStrategy($db, $table)
{
	$stmt = $db->prepare("SELECT COUNT(*) as count, MAX(`count`) as max FROM `" . $table . "`");
	$stmt->execute();
	$result = $stmt->fetch();
	return $result;
}


if (!isset($_GET['mode'])) die;

// 1 = Strategies Isolated
// 2 = Strategies
// 3 = Clues

$mode = (int)$_GET['mode'];
if ($mode !== 1 && $mode !== 2 && $mode !== 3) die;

try {
	$servername = "localhost";
	$username = "snovakow";
	$password = "kewbac-recge1-Fiwpux";
	$dbname = "sudoku";
	$db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

	$tableCount = 0;
	$puzzleCount = 0;
	$totalCount = 0;

	echo "<pre>";

	if ($mode === 1) {
		$tables = [];
		$stmt = $db->prepare("SELECT `table` FROM `tables`");
		$stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		foreach ($result as $key => $row) {
			$table = $row['table'];
			$tables[] = $table;

			$stmt = $db->prepare("SELECT MAX(id) as count FROM `" . $table . "`");
			$stmt->execute();
			$result = $stmt->fetch()["count"];
			$countTotal +=  $result;
		}

		flushOut("--- Strategies Isolated");

		$naked2 = queryStrategy($db, 'naked2');
		$naked3 = queryStrategy($db, 'naked3');
		$naked4 = queryStrategy($db, 'naked4');
		$hidden2 = queryStrategy($db, 'hidden2');
		$hidden3 = queryStrategy($db, 'hidden3');
		$hidden4 = queryStrategy($db, 'hidden4');
		$omissions = queryStrategy($db, 'omissions');
		$yWing = queryStrategy($db, 'yWing');
		$xyzWing = queryStrategy($db, 'xyzWing');
		$xWing = queryStrategy($db, 'xWing');
		$swordfish = queryStrategy($db, 'swordfish');
		$jellyfish = queryStrategy($db, 'jellyfish');
		$uniqueRectangle = queryStrategy($db, 'uniqueRectangle');

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
			printStat("naked2 (" . $naked2['max'] . ")", $naked2['count'], $candidates);
			printStat("naked3 (" . $naked3['max'] . ")", $naked3['count'], $candidates);
			printStat("naked4 (" . $naked4['max'] . ")", $naked4['count'], $candidates);
			printStat("hidden2 (" . $hidden2['max'] . ")", $hidden2['count'], $candidates);
			printStat("hidden3 (" . $hidden3['max'] . ")", $hidden3['count'], $candidates);
			printStat("hidden4 (" . $hidden4['max'] . ")", $hidden4['count'], $candidates);
			printStat("omissions (" . $omissions['max'] . ")", $omissions['count'], $candidates);
			printStat("uniqueRectangle (" . $uniqueRectangle['max'] . ")", $uniqueRectangle['count'], $candidates);
			printStat("yWing (" . $yWing['max'] . ")", $yWing['count'], $candidates);
			printStat("xyzWing (" . $xyzWing['max'] . ")", $xyzWing['count'], $candidates);
			printStat("xWing (" . $xWing['max'] . ")", $xWing['count'], $candidates);
			printStat("swordfish (" . $swordfish['max'] . ")", $swordfish['count'], $candidates);
			printStat("jellyfish (" . $jellyfish['max'] . ")", $jellyfish['count'], $candidates);
		}
		echo  "<br/>";
	} else {
		$stmt = $db->prepare("SELECT `tableCount`, `puzzleCount` FROM `tables`");
		$stmt->execute();
		$result = $stmt->fetch();
		$tableCount = (int)$result['tableCount'];
		$puzzleCount = (int)$result['puzzleCount'];
		$totalCount = totalCount($tableCount, $puzzleCount);
	}

	if ($mode === 2) {
		foreach ($tables as $table) {
			$stmt = $db->prepare("SELECT MAX(id) as count FROM `" . $table . "`");
			$stmt->execute();
			$result = $stmt->fetch()["count"];
			$countTotal +=  $result;
		}

		flushOut("--- Strategies");

		$strategies = [
			"naked2",
			"naked3",
			"naked4",
			"hidden2",
			"hidden3",
			"hidden4",
			"omissions",
			"uniqueRectangle",
			"yWing",
			"xyzWing",
			"xWing",
			"swordfish",
			"jellyfish"
		];

		$counts = [];
		$maxs = [];
		$candidates = 0;

		foreach ($strategies as $strategy) {
			$counts[$strategy] = 0;
			$maxs[$strategy] = 0;
		}

		foreach ($tables as $table) {
			foreach ($strategies as $strategy) {
				$sql = "SELECT MAX(`$strategy`) AS max, COUNT(`$strategy`) AS count
					FROM `$table` WHERE  `bruteForce`=0  AND `$strategy`>0";
				$stmt = $db->prepare($sql);
				$stmt->execute();
				$result = $stmt->fetch();

				$count = $result['count'];
				$counts[$strategy] += $count;
				$candidates += $count;

				$maxs[$strategy] =  max($maxs[$strategy], $result['max']);
			}
		}

		if ($candidates > 0) {
			foreach ($strategies as $strategy) {
				$count = $counts[$strategy];
				$max = $maxs[$strategy];
				printStat($strategy . " (" . $max . ")", $count, $candidates);
			}
		}

		echo  "<br/>";
	}

	if ($mode === 1 || $mode === 2) {
		$stmt = $db->prepare("SELECT MAX(id) as count FROM `simple`");
		$stmt->execute();
		$result = $stmt->fetch()["count"];
		$count0 = $result;

		$stmt = $db->prepare("SELECT MAX(id) as count FROM `bruteForce`");
		$stmt->execute();
		$result = $stmt->fetch()["count"];
		$count2 = $result;

		$count1 = $countTotal - $count0 - $count2;

		printStat("Simples", $count0, $countTotal);
		printStat("Strategies", $count1, $countTotal);
		printStat("Brute Force", $count2, $countTotal);

		echo  "<br/>";
	}

	$tableFormat = number_format($tableCount);
	$tableSyntax = $tableCount === 1 ? "table" : "tables";
	$totalFormat = number_format($totalCount);

	echo "$totalFormat puzzles in $tableFormat $tableSyntax<br/><br/>";

	if ($mode === 3) {
		flushOut("--- Clues");
		$counts = [];
		$countSimple = [];
		$countCandidate = [];
		$countUnsolvable = [];

		if ($tableCount > 1) {
			$unions = [];
			for ($i = 1; $i <= $tableCount; $i++) {
				$tableName = tableName($i);
				$unions[] = "SELECT * FROM `$tableName`";
			}
			$unionString = $unions . implode(' UNION ALL ', $unions);
			$puzzleString = "($unionString)";
		} else {
			$puzzleString = tableName(1);
		}
		$sql = "SELECT puzzles.`clueCount` as clueCount, puzzles.`solveType` as solveType, COUNT(*) as count FROM $puzzleString as puzzles
			GROUP BY clueCount, solveType";

		$stmt = $db->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($result as $key => $row) {
			$clueCount = $row['clueCount'];
			$solveType = (int)$row['solveType'];
			$count = (int)$row['count'];

			if (!$counts[$clueCount]) $counts[$clueCount] = 0;
			if (!$countSimple[$clueCount]) $countSimple[$clueCount] = 0;
			if (!$countCandidate[$clueCount]) $countCandidate[$clueCount] = 0;
			if (!$countUnsolvable[$clueCount]) $countUnsolvable[$clueCount] = 0;

			$counts[$clueCount] += $count;
			if ($solveType == 0) $countSimple[$clueCount] += $count;
			if ($solveType == 1) $countSimple[$clueCount] += $count;
			if ($solveType == 2) $countCandidate[$clueCount] += $count;
			if ($solveType == 3) $countCandidate[$clueCount] += $count;
			if ($solveType == 4) $countCandidate[$clueCount] += $count;
			if ($solveType == 5) $countUnsolvable[$clueCount] += $count;
		}

		foreach ($counts as $clueCount => $count) {
			printStat($clueCount, $count, $totalCount, 5);
		}
		echo  "<br/>";

		$countsSimple = 0;
		$countsCandidate = 0;
		$countsUnsolvable = 0;
		foreach ($countSimple as $clueCount => $count) $countsSimple += $count;
		foreach ($countCandidate as $clueCount => $count) $countsCandidate += $count;
		foreach ($countUnsolvable as $clueCount => $count) $countsUnsolvable += $count;

		printStat("Simple", $countsSimple, $totalCount, 1);
		foreach ($counts as $clueCount => $count) printStat($clueCount, $countSimple[$clueCount], $count, 5);
		echo  "<br/>";

		printStat("Candidate", $countsCandidate, $totalCount, 1);
		foreach ($counts as $clueCount => $count) printStat($clueCount, $countCandidate[$clueCount], $count, 5);
		echo  "<br/>";

		printStat("Unsolvable", $countsUnsolvable, $totalCount, 1);
		foreach ($counts as $clueCount => $count) printStat($clueCount, $countUnsolvable[$clueCount], $count, 5);
		echo  "<br/>";
	}
	echo "</pre>";
} catch (PDOException $e) {
	// echo "Error: " . $e->getMessage();
}
