<?php

function flushOut($message)
{
	echo "$message<br/>";
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
	$stat =  getStat($title, $count, $total);
	echo "$stat<br/>";
}

function queryStrategy($conn, $table)
{
	$stmt = $conn->prepare("SELECT COUNT(*) as count, MAX(`count`) as max FROM `" . $table . "`");
	$stmt->execute();
	$result = $stmt->fetch();
	return $result;
}

// header("Access-Control-Allow-Origin: *");

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

	$countTotal = 0;
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
		$tables = explode(",", $_GET['table']);
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

	$total = 0;
	$totals = [];
	foreach ($tables as $table) {
		$stmt = $db->prepare("SELECT MAX(id) as totalPuzzles FROM `" . $table . "`");
		$stmt->execute();
		$totalPuzzles = $stmt->fetch()["totalPuzzles"];
		if ($totalPuzzles === NULL) {
			$stmt = $db->prepare("
				SELECT table_name FROM information_schema.tables WHERE table_schema = 'sudoku' AND table_name = '" . $table . "' LIMIT 1;
			");
			$stmt->execute();
			if ($stmt->fetch()["table_name"] === NULL) $totalPuzzles = "-1";
			else $totalPuzzles = "0";
		} else {
			$total += $totalPuzzles;
		}

		$totals[] =  $table . "=" . $totalPuzzles;
		if ($mode !== 3) flushOut($table . ": " . number_format($totalPuzzles));
	}
		if (count($tables) > 1) flushOut("Total Puzzles: " . number_format($total));
		echo  "<br/>";

	if ($mode === 3) {
		flushOut("--- Clues");
		$counts = [];
		$count0 = [];
		$count1 = [];
		$count2 = [];
		foreach ($tables as $table) {
			$stmt = $db->prepare("SELECT `clueCount`, `solveType`, COUNT(*) as count FROM `" . $table . "` GROUP BY `clueCount`, `solveType`");
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
		echo  "<br/>";

		$counts0 = 0;
		$counts1 = 0;
		$counts2 = 0;
		foreach ($count0 as $clueCount => $count) $counts0 += $count;
		foreach ($count1 as $clueCount => $count) $counts1 += $count;
		foreach ($count2 as $clueCount => $count) $counts2 += $count;
		printStat("Simples", $counts0, $total);
		foreach ($counts as $clueCount => $count) printStat($clueCount, $count0[$clueCount], $count);
		echo  "<br/>";

		printStat("Strategies", $counts1, $total);
		foreach ($counts as $clueCount => $count) printStat($clueCount, $count1[$clueCount], $count);
		echo  "<br/>";

		printStat("Brute Force", $counts2, $total);
		foreach ($counts as $clueCount => $count) printStat($clueCount, $count2[$clueCount], $count);
		echo  "<br/>";
	}
} catch (PDOException $e) {
	// echo "Error: " . $e->getMessage();
}
