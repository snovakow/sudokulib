<?php

function flushSend()
{
	ob_flush();
	flush();
}
function flushOut($message, $flush)
{
	echo $message . "<br/>";
	if ($flush) flushSend();
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
	$stmt = $conn->prepare("SELECT COUNT(*) as count, MAX(count) as max FROM " . $table);
	$stmt->execute();
	$result = $stmt->fetch();
	return $result;
}

// header("Access-Control-Allow-Origin: *");
if (!isset($_GET['mode'])) die;

// -1 = All
// 0 = Count
// 1 = Clues
// 2 = Strategies
// 3 = Stats
$mode = (int)$_GET['mode'];
if ($mode < -1 || $mode > 5) die;

$flush = false;
if ($mode === -1) $flush = true;

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

try {
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_STRINGIFY_FETCHES);

	$table = "puzzles";
	if (isset($_GET['dbphistomefel'])) $table = "phistomefel";

	$counts = array();

	$stmt = $conn->prepare("
		SELECT COUNT(*) as totalPuzzles	FROM " . $table . " WHERE 1
	");
	$stmt->execute();
	$totalPuzzles = $stmt->fetch();
	$total = $totalPuzzles["totalPuzzles"];

	if ($mode === 1 || $mode === -1) {
		flushOut("--- Clues", $flush);

		$stmt = $conn->prepare("SELECT `clueCount`, COUNT(*) as count FROM " . $table . " GROUP BY `clueCount`");
		$stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($result as $key => $row) {
			$clueCount = $row['clueCount'];
			$count = $row['count'];

			printStat($clueCount, $count, $total);

			if ($mode === 1) {
				$stmtClue = $conn->prepare("SELECT `solveType`, COUNT(*) as count FROM " . $table . " WHERE clueCount=" . $clueCount . " GROUP BY `solveType`");
				$stmtClue->execute();
				$resultClue = $stmtClue->fetchAll(\PDO::FETCH_ASSOC);
				foreach ($resultClue as $key => $row) {
					$solveType = $row['solveType'];
					$countClue = $row['count'];

					$type = "Simples";
					if ($solveType === "1") $type =  "Strategies";
					else if ($solveType === "2") $type = "Brute Force";
					printStat("&nbsp;&nbsp;-" . $type, $countClue, $count);
				}
				flushSend();
			}
		}
		echo  "<br/>";
	}

	if ($mode === 2 || $mode === -1) {
		flushOut("--- Strategies", $flush);

		$stmt = $conn->prepare("
			SELECT
			SUM(`has_naked2`) AS naked2, MAX(`naked2`) AS max_naked2,
			SUM(`has_naked3`) AS naked3, MAX(`naked3`) AS max_naked3,
			SUM(`has_naked4`) AS naked4, MAX(`naked4`) AS max_naked4,
			SUM(`has_hidden2`) AS hidden2, MAX(`hidden2`) AS max_hidden2,
			SUM(`has_hidden3`) AS hidden3, MAX(`hidden3`) AS max_hidden3,
			SUM(`has_hidden4`) AS hidden4, MAX(`hidden4`) AS max_hidden4,
			SUM(`has_yWing`) AS yWing, MAX(`yWing`) AS max_yWing,
			SUM(`has_xyzWing`) AS xyzWing, MAX(`xyzWing`) AS max_xyzWing,
			SUM(`has_xWing`) AS xWing, MAX(`xWing`) AS max_xWing,
			SUM(`has_swordfish`) AS swordfish, MAX(`swordfish`) AS max_swordfish,
			SUM(`has_jellyfish`) AS jellyfish, MAX(`jellyfish`) AS max_jellyfish,
			SUM(`has_uniqueRectangle`) AS uniqueRectangle, MAX(`uniqueRectangle`) AS max_uniqueRectangle,
			SUM(`has_phistomefel`) AS phistomefel
			FROM " . $table . " WHERE `simple` = 0 AND `bruteForce` = 0
		");
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

		printStat("Naked 2 (" . $solveTypes['max_naked2']  . ")", $naked2, $markers);
		printStat("Naked 3 (" . $solveTypes['max_naked2']  . ")", $naked3, $markers);
		printStat("Naked 4 (" . $solveTypes['max_naked2']  . ")", $naked4, $markers);
		printStat("Hidden 2 (" . $solveTypes['max_hidden2']  . ")", $hidden2, $markers);
		printStat("Hidden 3 (" . $solveTypes['max_hidden2']  . ")", $hidden3, $markers);
		printStat("Hidden 4 (" . $solveTypes['max_hidden2']  . ")", $hidden4, $markers);
		printStat("yWing (" . $solveTypes['max_yWing']  . ")", $yWing, $markers);
		printStat("xyzWing (" . $solveTypes['max_xyzWing']  . ")", $xyzWing, $markers);
		printStat("xWing (" . $solveTypes['max_xWing']  . ")", $xWing, $markers);
		printStat("swordfish (" . $solveTypes['max_swordfish']  . ")", $swordfish, $markers);
		printStat("jellyfish (" . $solveTypes['max_jellyfish']  . ")", $jellyfish, $markers);
		printStat("uniqueRectangle (" . $solveTypes['max_uniqueRectangle']  . ")", $uniqueRectangle, $markers);
		printStat("phistomefel", $phistomefel, $markers);
		echo  "<br/>";

		if (isset($_GET['dbphistomefel'])) {
			flushSend();
			$phistomefel = queryStrategy($conn, 'phistomefelRing');
			printStat("Phistomefel Isolated", $phistomefel['count'], $total);
			echo  "<br/>";
		} else {
			flushOut("--- Strategies Isolated", $flush);
			$naked2 = queryStrategy($conn, 'naked2');
			$naked3 = queryStrategy($conn, 'naked3');
			$naked4 = queryStrategy($conn, 'naked4');
			$hidden2 = queryStrategy($conn, 'hidden2');
			$hidden3 = queryStrategy($conn, 'hidden3');
			$hidden4 = queryStrategy($conn, 'hidden4');
			$yWing = queryStrategy($conn, 'yWing');
			$xyzWing = queryStrategy($conn, 'xyzWing');
			$xWing = queryStrategy($conn, 'xWing');
			$swordfish = queryStrategy($conn, 'swordfish');
			$jellyfish = queryStrategy($conn, 'jellyfish');
			$uniqueRectangle = queryStrategy($conn, 'uniqueRectangle');

			$markers = 0;
			$markers += $naked2['count'];
			$markers += $naked3['count'];
			$markers += $naked4['count'];
			$markers += $hidden2['count'];
			$markers += $hidden3['count'];
			$markers += $hidden4['count'];
			$markers += $yWing['count'];
			$markers += $xyzWing['count'];
			$markers += $xWing['count'];
			$markers += $swordfish['count'];
			$markers += $jellyfish['count'];
			$markers += $uniqueRectangle['count'];
			$markers += $phistomefel['count'];

			printStat("Naked 2 (" . $naked2['max'] . ")", $naked2['count'], $markers);
			printStat("Naked 3 (" . $naked3['max'] . ")", $naked3['count'], $markers);
			printStat("Naked 4 (" . $naked4['max'] . ")", $naked4['count'], $markers);
			printStat("Hidden 2 (" . $hidden2['max'] . ")", $hidden2['count'], $markers);
			printStat("Hidden 3 (" . $hidden3['max'] . ")", $hidden3['count'], $markers);
			printStat("Hidden 4 (" . $hidden4['max'] . ")", $hidden4['count'], $markers);
			printStat("yWing (" . $yWing['max'] . ")", $yWing['count'], $markers);
			printStat("xyzWing (" . $xyzWing['max'] . ")", $xyzWing['count'], $markers);
			printStat("xWing (" . $xWing['max'] . ")", $xWing['count'], $markers);
			printStat("swordfish (" . $swordfish['max'] . ")", $swordfish['count'], $markers);
			printStat("jellyfish (" . $jellyfish['max'] . ")", $jellyfish['count'], $markers);
			printStat("uniqueRectangle (" . $uniqueRectangle['max'] . ")", $uniqueRectangle['count'], $markers);
			echo  "<br/>";
		}
	}

	if ($mode === 3 || $mode === -1) {
		flushOut("--- Stats", $flush);

		$stmt = $conn->prepare("SELECT `solveType`, COUNT(*) as count FROM " . $table . " GROUP BY `solveType`");
		$stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($result as $key => $row) {
			$solveType = $row['solveType'];
			$count = $row['count'];

			$type = "Simples";
			if ($solveType === "1") $type =  "Strategies";
			else if ($solveType === "2") $type = "Brute Force";
			printStat($type, $count, $total);
		}

		echo  "<br/>";
	}

	echo  "Total Puzzles: " . number_format($total) . "<br/>";
	echo  "<br/>";
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}
$conn = null;
