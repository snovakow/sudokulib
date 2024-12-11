<!doctype html>
<html>

<head>
	<title>Stats</title>
</head>

<body>
	<pre>
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

function tableStatement($tableCount, $countName, $tableName, $logic, $orderAsc = false)
{
	$sql = "";
	$sql .= "DROP TABLE IF EXISTS `$tableName`;\n";
	$sql .= "CREATE TABLE `$tableName` (\n";
	$sql .= "  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,\n";
	$sql .= "  `puzzle_id` int(10) unsigned NOT NULL,\n";
	$sql .= "  `count` tinyint(2) unsigned NOT NULL,\n";
	$sql .= "  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',\n";
	$sql .= "  PRIMARY KEY (`id`)\n";
	$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8;\n";

	$sql .= "INSERT INTO `$tableName` (`puzzle_id`, `count`, `table`)\n";

	$unions = [];
	for ($i = 1; $i <= $tableCount; $i++) {
		$table = tableName($i);
		$unions[] = "SELECT `id`, `$countName`, '$table' as puzzle FROM `$table` WHERE $logic";
	}
	$order = $orderAsc ? "ASC" : "DESC";
	$orderString = "ORDER BY `$countName` $order LIMIT 1000000";
	if (count($unions) === 1) {
		$unionString = $unions[0];
		$sql .= "$unionString $orderString;\n";
	} else {
		$unionString = implode(") \n  UNION ALL\n  (", $unions);
		$sql .= "SELECT `id`, `$countName`, `puzzle` FROM (\n  ($unionString)\n  $orderString\n) as puzzles;\n";
	}

	$sql .= "ALTER TABLE `$tableName` AUTO_INCREMENT=1;\n";
	return $sql;
}

function strategyLogic($strategy, $priority = "")
{
	if ($strategy == $priority) return " AND `$strategy`>0";
	return " AND `$strategy`=0";
}
function tableLogic($strategy = 0)
{
	$logic = "";
	$logic .= strategyLogic("naked2", $strategy);
	$logic .= strategyLogic("naked3", $strategy);
	$logic .= strategyLogic("naked4", $strategy);
	$logic .= strategyLogic("hidden1", $strategy);
	$logic .= strategyLogic("hidden2", $strategy);
	$logic .= strategyLogic("hidden3", $strategy);
	$logic .= strategyLogic("hidden4", $strategy);
	$logic .= strategyLogic("omissions", $strategy);
	$logic .= strategyLogic("uniqueRectangle", $strategy);
	$logic .= strategyLogic("yWing", $strategy);
	$logic .= strategyLogic("xyzWing", $strategy);
	$logic .= strategyLogic("xWing", $strategy);
	$logic .= strategyLogic("swordfish", $strategy);
	$logic .= strategyLogic("jellyfish", $strategy);
	return $logic;
}

function tableStrategyLogic($tableCount, $solveType, $strategy, $tableName)
{
	$logic = "`solveType`=$solveType";
	$logic .= tableLogic($strategy);
	$sql = tableStatement($tableCount, $strategy, $tableName, $logic);
	return "$sql\n";
}

if (!isset($_GET['mode'])) die;

// 0 = Populate Statements
// 1 = Populated Tables
// 2 = Totals
// 3 = Simples
// 4 = Visuals
// 5 = Strategies
// 6 = Clues

$mode = (int)$_GET['mode'];
if (!is_int($mode) || $mode < 0 || $mode > 5) die;

try {
	$servername = "localhost";
	$username = "snovakow";
	$password = "kewbac-recge1-Fiwpux";
	$dbname = "sudoku";
	$db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

	$tableCount = 0;
	$puzzleCount = 0;
	$totalCount = 0;

	$time = time();

	$stmt = $db->prepare("SELECT `tableCount`, `puzzleCount` FROM `tables`");
	$stmt->execute();
	$result = $stmt->fetch();
	$tableCount = (int)$result['tableCount'];
	$puzzleCount = (int)$result['puzzleCount'];
	$totalCount = totalCount($tableCount, $puzzleCount);

	if ($mode === 0) {
		$logic = "`solveType`=0 AND `omissionSimple`=0 AND `nakedSimple`=0";
		$sql = tableStatement($tableCount, "hiddenSimple", "simple_hidden", $logic, true);
		echo "$sql\n";

		$logic = "`solveType`=0 AND `omissionSimple`>0 AND `nakedSimple`=0";
		$sql = tableStatement($tableCount, "omissionSimple", "simple_omission", $logic);
		echo "$sql\n";

		$logic = "`solveType`=0 AND `omissionSimple`=0 AND `nakedSimple`>0";
		$sql = tableStatement($tableCount, "nakedSimple", "simple_naked", $logic);
		echo "$sql\n";

		$logic = "`solveType`=1";
		$sql = tableStatement($tableCount, "nakedVisible", "candidate_visible", $logic);
		echo "$sql\n";

		echo tableStrategyLogic($tableCount, 3, "naked2", "candidate_naked2");
		echo tableStrategyLogic($tableCount, 3, "naked3", "candidate_naked3");
		echo tableStrategyLogic($tableCount, 3, "naked4", "candidate_naked4");
		echo tableStrategyLogic($tableCount, 3, "hidden1", "candidate_hidden1");
		echo tableStrategyLogic($tableCount, 3, "hidden2", "candidate_hidden2");
		echo tableStrategyLogic($tableCount, 3, "hidden3", "candidate_hidden3");
		echo tableStrategyLogic($tableCount, 3, "hidden4", "candidate_hidden4");
		echo tableStrategyLogic($tableCount, 3, "omissions", "candidate_omissions");
		echo tableStrategyLogic($tableCount, 3, "uniqueRectangle", "candidate_uniqueRectangle");
		echo tableStrategyLogic($tableCount, 3, "yWing", "candidate_yWing");
		echo tableStrategyLogic($tableCount, 3, "xyzWing", "candidate_xyzWing");
		echo tableStrategyLogic($tableCount, 3, "xWing", "candidate_xWing");
		echo tableStrategyLogic($tableCount, 3, "swordfish", "candidate_swordfish");
		echo tableStrategyLogic($tableCount, 3, "jellyfish", "candidate_jellyfish");

		$logic = "`solveType`=4";
		$logic .= strategyLogic("hiddenSimple");
		$logic .= strategyLogic("omissionSimple");
		$logic .= strategyLogic("nakedSimple");
		$logic .= strategyLogic("nakedVisible");
		$logic .= strategyLogic("omissionVisible");
		$logic .= tableLogic();
		$sql = tableStatement($tableCount, "clueCount", "unsolvable", $logic);
		echo "$sql\n";

		// Show count vs max
		$unions = [];
		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$unions[] = "SELECT COUNT(`id`) as count, Max(`id`) as max FROM `$table`";
		}
		if (count($unions) === 1) {
			$unionString = $unions[0];
			$sql = "$unionString;";
		} else {
			$unionString = implode("\n UNION ALL\n ", $unions);
			$sql = "SELECT FORMAT(SUM(`count`),0) AS 'count', FORMAT(SUM(`max`),0) AS 'max' FROM (\n $unionString\n)";
			$sql .= " as puzzles;";
		}
		echo "$sql\n";
	}

	if ($mode === 1) {
		flushOut("--- Strategies Isolated");
		$sql3 = "SELECT 
		`naked2`>0 as naked2Count, MAX(`naked2`) as naked2Max, 
		`naked3`>0 as naked3Count, MAX(`naked3`) as naked3Max, 
		`naked4`>0 as naked4Count, MAX(`naked4`) as naked4Max, 
		`hidden1`>0 as hidden1Count, MAX(`hidden1`) as hidden1Max, 
		`hidden2`>0 as hidden2Count, MAX(`hidden2`) as hidden1Max, 
		`hidden3`>0 as hidden3Count, MAX(`hidden3`) as hidden1Max, 
		`hidden4`>0 as hidden4Count, MAX(`hidden4`) as hidden1Max, 
		`omissions`>0 as omissionsCount, MAX(`omissions`) as omissionsMax, 
		`uniqueRectangle`>0 as uniqueRectangleCount, MAX(`uniqueRectangle`) as uniqueRectangleMax, 
		`yWing`>0 as yWingCount, MAX(`yWing`) as yWingMax, 
		`xyzWing`>0 as xyzWingCount, MAX(`xyzWing`) as xyzWingMax, 
		`xWing`>0 as xWingCount, MAX(`xWing`) as xWingMax, 
		`swordfish`>0 as swordfishCount, MAX(`swordfish`) as swordfishMax, 
		`jellyfish`>0 as jellyfishCount, MAX(`jellyfish`) as jellyfishMax, 
		`solveType`, COUNT(*) as count FROM `puzzles001`
		WHERE `solveType`=2 OR `solveType`=3
		GROUP BY naked2Count, naked3Count, naked4Count, hidden1Count, hidden2Count, hidden3Count, hidden4Count, 
		omissionsCount, uniqueRectangleCount, yWingCount, xyzWingCount, xWingCount, swordfishCount, jellyfishCount, `solveType`";

		$strategies = [
			"simple",
			"simple_all",
		];

		$strategiesCount = [
			"simple_hidden",
			"simple_omission",
			"simple_naked",
			"candidate_visible",
			"candidate_naked2",
			"candidate_naked3",
			"candidate_naked4",
			"candidate_hidden1",
			"candidate_hidden2",
			"candidate_hidden3",
			"candidate_hidden4",
			"candidate_omissions",
			"candidate_uniqueRectangle",
			"candidate_yWing",
			"candidate_xyzWing",
			"candidate_xWing",
			"candidate_swordfish",
			"candidate_jellyfish",
			"unsolvable",
		];
		$strategies = [
			"simple_hidden",
			"unsolvable",
		];

		// $naked2 = queryStrategy($db, 'naked2');
		// $naked3 = queryStrategy($db, 'naked3');
		// $naked4 = queryStrategy($db, 'naked4');
		// $hidden2 = queryStrategy($db, 'hidden2');
		// $hidden3 = queryStrategy($db, 'hidden3');
		// $hidden4 = queryStrategy($db, 'hidden4');
		// $omissions = queryStrategy($db, 'omissions');
		// $yWing = queryStrategy($db, 'yWing');
		// $xyzWing = queryStrategy($db, 'xyzWing');
		// $xWing = queryStrategy($db, 'xWing');
		// $swordfish = queryStrategy($db, 'swordfish');
		// $jellyfish = queryStrategy($db, 'jellyfish');
		// $uniqueRectangle = queryStrategy($db, 'uniqueRectangle');

		// $candidates = 0;
		// $candidates += $naked2['count'];
		// $candidates += $naked3['count'];
		// $candidates += $naked4['count'];
		// $candidates += $hidden2['count'];
		// $candidates += $hidden3['count'];
		// $candidates += $hidden4['count'];
		// $candidates += $omissions['count'];
		// $candidates += $yWing['count'];
		// $candidates += $xyzWing['count'];
		// $candidates += $xWing['count'];
		// $candidates += $swordfish['count'];
		// $candidates += $jellyfish['count'];
		// $candidates += $uniqueRectangle['count'];

		// if ($candidates > 0) {
		// 	printStat("naked2 (" . $naked2['max'] . ")", $naked2['count'], $candidates);
		// 	printStat("naked3 (" . $naked3['max'] . ")", $naked3['count'], $candidates);
		// 	printStat("naked4 (" . $naked4['max'] . ")", $naked4['count'], $candidates);
		// 	printStat("hidden2 (" . $hidden2['max'] . ")", $hidden2['count'], $candidates);
		// 	printStat("hidden3 (" . $hidden3['max'] . ")", $hidden3['count'], $candidates);
		// 	printStat("hidden4 (" . $hidden4['max'] . ")", $hidden4['count'], $candidates);
		// 	printStat("omissions (" . $omissions['max'] . ")", $omissions['count'], $candidates);
		// 	printStat("uniqueRectangle (" . $uniqueRectangle['max'] . ")", $uniqueRectangle['count'], $candidates);
		// 	printStat("yWing (" . $yWing['max'] . ")", $yWing['count'], $candidates);
		// 	printStat("xyzWing (" . $xyzWing['max'] . ")", $xyzWing['count'], $candidates);
		// 	printStat("xWing (" . $xWing['max'] . ")", $xWing['count'], $candidates);
		// 	printStat("swordfish (" . $swordfish['max'] . ")", $swordfish['count'], $candidates);
		// 	printStat("jellyfish (" . $jellyfish['max'] . ")", $jellyfish['count'], $candidates);
		// }
		// echo  "<br/>";
	}

	if ($mode === 2) {
		$number = number_format($totalCount);
		flushOut("--- Total $number");

		$unions = [];
		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$unions[] = "SELECT `solveType`, COUNT(*) as count FROM `$table` GROUP BY `solveType`";
		}
		if (count($unions) === 1) {
			$unionString = $unions[0];
			$sql = "$unionString;\n";
		} else {
			$unionString = implode("\n UNION ALL\n ", $unions);
			$sql = "SELECT `solveType`, SUM(`count`) as count FROM\n($unionString\n)";
			$sql .= " as puzzles GROUP BY `solveType`;\n";
		}
		// echo $unions[0], ";\n";
		// echo "$sql\n";

		$counts = [];

		$stmt = $db->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($result as $key => $row) {
			$solveType = (int)$row['solveType'];
			$count = (int)$row['count'];
			$counts[$solveType] = $count;
		}

		$simple = $counts[0];

		$candidateVisual = $counts[1];
		$candidate = $counts[2];
		$candidateMinimal = $counts[3];
		$candidate += $candidateMinimal;
		$unsolvable = $counts[4];

		$percent = percentage($simple, $totalCount, 2);
		$number = number_format($simple);
		echo "Simple: $percent $number\n";

		$percent = percentage($candidateVisual, $totalCount, 2);
		$percentVisual = percentage($candidateVisual, $candidateVisual + $candidate, 2);
		$number = number_format($candidateVisual);
		echo "Visual: $percent ($percentVisual of candidates) $number\n";

		$percent = percentage($candidate, $totalCount, 2);
		$percentMinimal = percentage($candidateMinimal, $candidate, 2);
		$number = number_format($candidate);
		echo "Strategy: $percent ($percentMinimal minimal) $number\n";

		$percent = percentage($unsolvable, $totalCount, 2);
		$number = number_format($unsolvable);
		echo "Unsolvable: $percent $number\n";

		echo "\n";
	}

	if ($mode === 3) {
		$unions = [];
		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$sql = "SELECT ";
			$sql .= "`hiddenSimple`>0 as hiddenSimple, MAX(`hiddenSimple`) as hiddenSimpleMax, ";
			$sql .= "`omissionSimple`>0 as omissionSimple, MAX(`omissionSimple`) as omissionSimpleMax, ";
			$sql .= "`nakedSimple`>0 as nakedSimple, MAX(`nakedSimple`) as nakedSimpleMax, ";
			$sql .= "COUNT(*) as count FROM `$table` WHERE solveType=0 ";
			$sql .= "GROUP BY hiddenSimple, omissionSimple, nakedSimple";
			$unions[] = $sql;
		}
		$unionString = implode(" UNION ALL ", $unions);
		$sql = "SELECT ";
		$sql .= "hiddenSimple, MAX(hiddenSimpleMax) as hiddenSimpleMax, ";
		$sql .= "omissionSimple, MAX(omissionSimpleMax) as omissionSimpleMax, ";
		$sql .= "nakedSimple, MAX(nakedSimpleMax) as nakedSimpleMax, ";
		$sql .= "SUM(`count`) as count FROM ($unionString) as puzzles ";
		$sql .= "GROUP BY hiddenSimple, omissionSimple, nakedSimple";

		$stmt = $db->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		$total = 0;
		$counts = [];
		$countsIsolated = [];
		$maxs = [];
		$maxsIsolated = [];
		$maxsAll = [];
		for ($i = 0; $i < 3; $i++) {
			$counts[$i] = 0;
			$countsIsolated[$i] = 0;
			$maxs[$i] = 0;
			$maxsIsolated[$i] = 0;
			$maxsAll[$i] = 0;
		}
		$countsIsolated[3] = 0;

		$results = [];
		foreach ($result as $row) {
			$hiddenSimple = (int)$row['hiddenSimple'];
			$omissionSimple = (int)$row['omissionSimple'];
			$nakedSimple = (int)$row['nakedSimple'];
			$hiddenSimpleMax = (int)$row['hiddenSimpleMax'];
			$omissionSimpleMax = (int)$row['omissionSimpleMax'];
			$nakedSimpleMax = (int)$row['nakedSimpleMax'];
			$count = (int)$row['count'];

			if ($hiddenSimple > 0) $counts[0] += $count;
			if ($omissionSimple > 0) $counts[1] += $count;
			if ($nakedSimple > 0) $counts[2] += $count;
			if ($hiddenSimpleMax > $maxs[0]) $maxs[0] = $hiddenSimpleMax;
			if ($omissionSimpleMax > $maxs[1]) $maxs[1] = $omissionSimpleMax;
			if ($nakedSimpleMax > $maxs[2]) $maxs[2] = $nakedSimpleMax;

			if ($omissionSimple == 0 && $nakedSimple == 0) {
				$countsIsolated[0] += $count;
				if ($hiddenSimpleMax > $maxsIsolated[0]) $maxsIsolated[0] = $hiddenSimpleMax;
			}
			if ($omissionSimple > 0 && $nakedSimple == 0) {
				$countsIsolated[1] += $count;
				if ($omissionSimpleMax > $maxsIsolated[1]) $maxsIsolated[1] = $omissionSimpleMax;
			}
			if ($omissionSimple == 0 && $nakedSimple > 0) {
				$countsIsolated[2] += $count;
				if ($nakedSimpleMax > $maxsIsolated[2]) $maxsIsolated[2] = $nakedSimpleMax;
			}
			if ($omissionSimple > 0 && $nakedSimple > 0) {
				$countsIsolated[3] += $count;

				if ($hiddenSimpleMax > $maxsAll[0]) $maxsAll[0] = $hiddenSimpleMax;
				if ($omissionSimpleMax > $maxsAll[1]) $maxsAll[1] = $omissionSimpleMax;
				if ($nakedSimpleMax > $maxsAll[2]) $maxsAll[2] = $nakedSimpleMax;
			}

			$total += $count;
		}

		$percent = percentage($total, $totalCount, 2);
		$number = number_format($total);
		echo "--- Simples $percent $number\n";

		$count = $counts[0];
		$percent = percentage($count, $total, 2);
		$max = number_format($maxs[0]);
		$format = number_format($count);
		echo "Hidden: $percent ($max) $format\n";

		$count = $counts[1];
		$percent = percentage($count, $total, 2);
		$max = number_format($maxs[1]);
		$format = number_format($count);
		echo "Omission: $percent ($max) $format\n";

		$max = number_format($maxs[2]);
		$count = $counts[2];
		$percent = percentage($count, $total, 2);
		$format = number_format($count);
		echo "Naked: $percent ($max) $format\n";

		echo "\nIsolated\n";

		if ($omissionSimple == 0 && $nakedSimple == 0) {
			$percent = percentage($count, $total, 2);
			$max = number_format($hiddenSimpleMax);
			$format = number_format($count);
			$results[0] = "Hidden: $percent ($max) $format\n";
		}

		foreach ($result as $row) {
			$hiddenSimple = (int)$row['hiddenSimple'];
			$omissionSimple = (int)$row['omissionSimple'];
			$nakedSimple = (int)$row['nakedSimple'];
			$hiddenSimpleMax = (int)$row['hiddenSimpleMax'];
			$omissionSimpleMax = (int)$row['omissionSimpleMax'];
			$nakedSimpleMax = (int)$row['nakedSimpleMax'];
			$count = (int)$row['count'];

			$format = number_format($count);
			if ($omissionSimple == 0 && $nakedSimple == 0) {
				$percent = percentage($count, $total, 2);
				$max = number_format($hiddenSimpleMax);
				$results[0] = "Hidden: $percent ($max) $format\n";
			}
			if ($omissionSimple > 0 && $nakedSimple == 0) {
				$percent = percentage($count, $total, 2);
				$max = number_format($omissionSimpleMax);
				$results[1] = "Omission: $percent ($max) $format\n";
			}
			if ($omissionSimple == 0 && $nakedSimple > 0) {
				$percent = percentage($count, $total, 2);
				$max = number_format($nakedSimpleMax);
				$results[2] = "Naked: $percent ($max) $format\n";
			}
			if ($omissionSimple > 0 && $nakedSimple > 0) {
				$percent = percentage($count, $total, 2);
				$results[3] = "All: $percent $format\n";

				$percent = percentage($countsAll[0], $count, 2);
				$max = number_format($hiddenSimpleMax);
				$results[4] = "  ($max Hidden)\n";

				$percent = percentage($countsAll[1], $count, 2);
				$max = number_format($omissionSimpleMax);
				$results[5] = "  ($max Omission)\n";

				$percent = percentage($countsAll[2], $count, 2);
				$max = number_format($nakedSimpleMax);
				$results[6] = "  ($max Naked)\n";
			}
		}
		echo $results[0];
		echo $results[1];
		echo $results[2];
		echo $results[3];
		echo $results[4];
		echo $results[5];
		echo $results[6];

		echo "\n";
	}

	if ($mode === 4) {
		flushOut("--- Visual");

		$unions = [];
		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$sql = "SELECT ";
			$sql .= "`nakedVisible`>0 as nakedVisible, MAX(`nakedVisible`) as nakedVisibleMax, ";
			$sql .= "`omissionVisible`>0 as omissionVisible, MAX(`omissionVisible`) as omissionVisibleMax, ";
			$sql .= "COUNT(*) as count FROM `$table` WHERE solveType=1 ";
			$unions[] = $sql;
		}
		$unionString = implode(" UNION ALL ", $unions);
		$sql = "SELECT ";
		$sql .= "nakedVisible, MAX(nakedVisibleMax) as nakedVisibleMax, ";
		$sql .= "omissionVisible, MAX(omissionVisibleMax) as omissionVisibleMax, ";
		$sql .= "SUM(`count`) as count FROM ($unionString) as puzzles ";
		$sql .= "GROUP BY nakedVisible, omissionVisible";

		$stmt = $db->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		$total = 0;
		$counts = [];
		$maxs = [];
		for ($i = 0; $i < 2; $i++) {
			$counts[$i] = 0;
			$maxs[$i] = 0;
		}

		$results = [];
		foreach ($result as $row) {
			$nakedVisible = (int)$row['nakedVisible'];
			$omissionVisible = (int)$row['omissionVisible'];
			$nakedVisibleMax = (int)$row['nakedVisibleMax'];
			$omissionVisibleMax = (int)$row['omissionVisibleMax'];
			$count = (int)$row['count'];

			if ($nakedVisible > 0) $counts[0] += $count;
			if ($omissionVisible > 0) $counts[1] += $count;
			if ($nakedVisibleMax > $maxs[0]) $maxs[0] = $nakedVisibleMax;
			if ($omissionVisibleMax > $maxs[1]) $maxs[1] = $omissionVisibleMax;

			$total += $count;
		}

		$count = $counts[0];
		$percent = percentage($count, $total, 2);
		$max = number_format($maxs[0]);
		$format = number_format($count);
		echo "Naked: $percent ($max) $format\n";

		$count = $counts[1];
		$percent = percentage($count, $total, 2);
		$max = number_format($maxs[1]);
		$format = number_format($count);
		echo "Omission: $percent ($max) $format\n";

		echo $results[0];
		echo $results[1];

		echo "\n";
	}

	if ($mode === 5) {
		flushOut("--- Strategies");
		// $percent = percentage($candidateVisual, $totalCount, 2);
		// $percentVisual = percentage($candidateVisual, $candidateVisual + $candidate, 2);
		// echo "Visual: $percent ($percentVisual of candidates)\n";

		// $percent = percentage($candidate, $totalCount, 2);
		// $percentMinimal = percentage($candidateMinimal, $candidate, 2);
		// echo "Strategy: $percent ($percentMinimal minimal)\n";

		// $percent = percentage($unsolvable, $totalCount, 2);
		// echo "Unsolvable: $percent\n";

		// const res = 10000;
		// const percent = (val, total = this.totalPuzzles) => {
		// 	return ((Math.ceil(100 * res * val / total) / res).toFixed(3) + "%").padStart(7, "0");
		// }
		// const makeLineSimple = (title, val, total) => {
		// 	return title + ": " + percent(val, total);
		// };

		// const lines = [];

		// if (this.simplesMinimal.count > 0) {
		// 	lines.push("");
		// 	lines.push("--- Simples Minimal");
		// 	const printStrategy = (title, property) => {
		// 		let line = makeLineSimple(title, this.simplesIsolated[property], this.simplesMinimal.count);
		// 		line += " - " + this.simplesIsolated[property].toLocaleString();
		// 		lines.push(line);
		// 	}
		// 	printStrategy("Hidden", 'hiddenSimple');
		// 	printStrategy("Omission", 'omissionSimple');
		// 	printStrategy("Naked", 'nakedSimple');
		// 	printStrategy("All", 'allSimple');
		// }

		// if (this.candidatesMinimal.count) {
		// 	lines.push("");
		// 	lines.push("--- Candidates Minimal");

		// 	const printStrategy = (title, property) => {
		// 		const line = makeLineSimple(title, this.candidatesMinimal[property], this.candidatesMinimal.count);
		// 		lines.push(line + " - " + this.candidatesMinimal[property].toLocaleString());
		// 	}
		// 	printStrategy("Naked2", 'naked2');
		// 	printStrategy("Naked3", 'naked3');
		// 	printStrategy("Naked4", 'naked4');
		// 	printStrategy("Hidden1", 'hidden1');
		// 	printStrategy("Hidden2", 'hidden2');
		// 	printStrategy("Hidden3", 'hidden3');
		// 	printStrategy("Hidden4", 'hidden4');
		// 	printStrategy("Omissions", 'omissions');
		// 	printStrategy("Unique Rectangle", 'uniqueRectangle');
		// 	printStrategy("Y-Wing", 'yWing');
		// 	printStrategy("XYZ-Wing", 'xyzWing');
		// 	printStrategy("X-Wing", 'xWing');
		// 	printStrategy("Swordfish", 'swordfish');
		// 	printStrategy("Jellyfish", 'jellyfish');
		// }

		$sql = "SELECT 
			puzzles.`naked2`>0 as naked2, MAX(puzzles.`naked2`) as naked2Max, 
			puzzles.`naked3`>0 as naked3, MAX(puzzles.`naked3`) as naked3Max, 
			puzzles.`naked4`>0 as naked4, MAX(puzzles.`naked4`) as naked4Max, 
			puzzles.`hidden1`>0 as hidden1, MAX(puzzles.`hidden1`) as hidden1Max, 
			puzzles.`hidden2`>0 as hidden2, MAX(puzzles.`hidden2`) as hidden1Max, 
			puzzles.`hidden3`>0 as hidden3, MAX(puzzles.`hidden3`) as hidden1Max, 
			puzzles.`hidden4`>0 as hidden4, MAX(puzzles.`hidden4`) as hidden1Max, 
			puzzles.`omissions`>0 as omissions, MAX(puzzles.`omissions`) as omissionsMax, 
			puzzles.`uniqueRectangle`>0 as uniqueRectangle, MAX(puzzles.`uniqueRectangle`) as uniqueRectangleMax, 
			puzzles.`yWing`>0 as yWing, MAX(puzzles.`yWing`) as yWingMax, 
			puzzles.`xyzWing`>0 as xyzWing, MAX(puzzles.`xyzWing`) as xyzWingMax, 
			puzzles.`xWing`>0 as xWing, MAX(puzzles.`xWing`) as xWingMax, 
			puzzles.`swordfish`>0 as swordfish, MAX(puzzles.`swordfish`) as swordfishMax, 
			puzzles.`jellyfish`>0 as jellyfish, MAX(puzzles.`jellyfish`) as jellyfishMax, 
			puzzles.`solveType` as solveType, COUNT(*) as count FROM ppuzzles001 as puzzles
			WHERE solveType=2 OR solveType=3
			GROUP BY naked2, naked3, naked4, hidden1, hidden2, hidden3, hidden4, 
			omissions, uniqueRectangle, yWing, xyzWing, xWing, swordfish, jellyfish, solveType";

		$strategies = [
			"simple",
			"simple_all",
			"candidate",
			"unsolvable",
		];

		$strategiesCount = [
			"simple_hidden",
			"simple_omission",
			"simple_naked",
			"candidate_visible",
			"candidate_naked2",
			"candidate_naked3",
			"candidate_naked4",
			"candidate_hidden1",
			"candidate_hidden2",
			"candidate_hidden3",
			"candidate_hidden4",
			"candidate_omissions",
			"candidate_uniqueRectangle",
			"candidate_yWing",
			"candidate_xyzWing",
			"candidate_xWing",
			"candidate_swordfish",
			"candidate_jellyfish",
		];

		$strategiesAll = [
			"simple",
			"simple_all",
			"candidate",
			"unsolvable",
			"simple_hidden",
			"simple_omission",
			"simple_naked",
			"candidate_visible",
			"candidate_naked2",
			"candidate_naked3",
			"candidate_naked4",
			"candidate_hidden1",
			"candidate_hidden2",
			"candidate_hidden3",
			"candidate_hidden4",
			"candidate_omissions",
			"candidate_uniqueRectangle",
			"candidate_yWing",
			"candidate_xyzWing",
			"candidate_xWing",
			"candidate_swordfish",
			"candidate_jellyfish",
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

	if ($mode === 6) {
		flushOut("--- Clues");
		$counts = [];
		$countSimple = [];
		$countCandidate = [];
		$countUnsolvable = [];

		$unions = [];
		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$unions[] = "SELECT `clueCount`, `solveType`, COUNT(*) as count FROM `$table` GROUP BY `clueCount`, `solveType`";
		}
		if (count($unions) === 1) {
			$unionString = $unions[0];
			$sql = "$unionString;\n";
		} else {
			$unionString = implode("\n UNION ALL\n ", $unions);
			$sql = "SELECT `clueCount`, `solveType`, SUM(`count`) as count FROM\n($unionString\n)";
			$sql .= " as puzzles GROUP BY `clueCount`, `solveType`;\n";
		}
		// echo $unions[0], ";\n";
		// echo "$sql\n";

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
			if ($solveType == 1) $countCandidate[$clueCount] += $count;
			if ($solveType == 2) $countCandidate[$clueCount] += $count;
			if ($solveType == 3) $countCandidate[$clueCount] += $count;
			if ($solveType == 4) $countUnsolvable[$clueCount] += $count;
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

		printStat("Simple", $countsSimple, $totalCount, 2);
		foreach ($counts as $clueCount => $count) printStat($clueCount, $countSimple[$clueCount], $count, 2);
		echo  "<br/>";

		printStat("Candidate", $countsCandidate, $totalCount, 2);
		foreach ($counts as $clueCount => $count) printStat($clueCount, $countCandidate[$clueCount], $count, 2);
		echo  "<br/>";

		printStat("Unsolvable", $countsUnsolvable, $totalCount, 2);
		foreach ($counts as $clueCount => $count) printStat($clueCount, $countUnsolvable[$clueCount], $count, 2);
		echo  "<br/>";
	}

	if ($mode > 1) {
		$time = (time() - $time) . "s";
		echo $time;
	}
} catch (PDOException $e) {
	// echo "Error: " . $e->getMessage();
}
?></pre>
</body>

</html>