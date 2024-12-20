<!doctype html>
<html>

<head>
	<title>Stats</title>
</head>

<body>
	<pre>
<?php

const MAX_SIZE = 10000000;

// 0 = Update Prep Statements
// 1 = Populate Statements
// 2 = Populated Tables
// 3 = Totals
// 4 = Simples Visuals
// 5 = Strategies
// 6 = Clues

function totalCount($tableCount, $puzzleCount)
{
	if ($tableCount === 0) return 0;
	return (($tableCount - 1) * MAX_SIZE) +  $puzzleCount;
}

function tableName($number, $append = "")
{
	$pad = str_pad($number, 3, "0", STR_PAD_LEFT);
	return "puzzles$append$pad";
}

function percentage($count, $total, $precision, $pad = 3)
{
	$percent = number_format(100.0 * $count / $total, $precision, '.', "");
	$pad = str_pad($percent, $precision + $pad, "0", STR_PAD_LEFT);
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
	$stmt = $db->prepare("SELECT COUNT(*) AS count, MAX(`count`) AS max FROM `" . $table . "`");
	$stmt->execute();
	$result = $stmt->fetch();
	return $result;
}

function tableStatement($tableCount, $countName, $tableName, $logic, $select = null)
{
	if ($select === null) $select = "`$countName`";

	$sql = "";
	$sql .= "DROP TABLE IF EXISTS `$tableName`;\n";
	$sql .= "CREATE TABLE `$tableName` (\n";
	$sql .= "  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,\n";
	$sql .= "  `count` tinyint(2) unsigned NOT NULL,\n";
	$sql .= "  `puzzle_id` int(10) unsigned NOT NULL,\n";
	$sql .= "  `table_id` int(10) unsigned NOT NULL,\n";
	$sql .= "  PRIMARY KEY (`id`)\n";
	$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=ascii;\n";
	$sql .= "INSERT INTO `$tableName` (`count`, `puzzle_id`, `table_id`)\n";

	$unions = [];
	for ($table_id = 1; $table_id <= $tableCount; $table_id++) {
		$table = tableName($table_id);
		$unions[] = "SELECT $select, `id`, '$table_id' AS puzzle FROM `$table` WHERE $logic";
	}
	$orderString = "ORDER BY `$countName` DESC LIMIT 1000000";
	if (count($unions) === 1) {
		$unionString = $unions[0];
		$sql .= "$unionString $orderString;\n";
	} else {
		$unionString = implode(") \n  UNION ALL\n  (", $unions);
		$sql .= "SELECT `$countName`, `id`, `puzzle` FROM (\n  ($unionString)\n  $orderString\n) AS puzzles;\n";
	}

	$sql .= "ALTER TABLE `$tableName` AUTO_INCREMENT=1;\n";
	return $sql;
}

function strategyLogic($strategy, $priority = "")
{
	if ($strategy == $priority) return " AND `$strategy`>0";
	return " AND `$strategy`=0";
}
function tableLogic($strategy = "")
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

$mode = (int)$_GET['mode'];
if (!is_int($mode) || $mode < 0 || $mode > 6) die;

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
		$tableNames = [];
		for ($i = 1; $i <= $tableCount; $i++) {
			$tableName = tableName($i);

			$rename = tableName($i, "_bu");

			$sql = "DROP TABLE IF EXISTS `$rename`;";
			echo "$sql\n";

			$sql = "RENAME TABLE $tableName TO $rename;";
			echo "$sql\n";

			$sql = "CREATE TABLE `$tableName` (
  `id` int(10) unsigned NOT NULL,
  `puzzleData` binary(32) NOT NULL DEFAULT '00000000000000000000000000000000',
  `clueCount` tinyint(2) unsigned NOT NULL,
  `solveType` tinyint(1) unsigned NOT NULL,
  `hiddenSimple` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `omissionSimple` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `naked2Simple` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `naked3Simple` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `nakedSimple` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `omissionVisible` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `nakedVisible` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `naked2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `naked3` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `naked4` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hidden1` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hidden2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hidden3` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hidden4` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `omissions` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `uniqueRectangle` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `yWing` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `xyzWing` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `xWing` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `swordfish` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `jellyfish` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin;";
			echo "$sql\n";

			$sql = "INSERT INTO `$tableName` (`id`, `puzzleData`, `clueCount`, `solveType`, 
  `hiddenSimple`, `omissionSimple`, `naked2Simple`, `naked3Simple`, `nakedSimple`, 
  `omissionVisible`, `nakedVisible`, 
  `naked2`, `naked3`, `naked4`, `hidden1`, `hidden2`, `hidden3`, `hidden4`, 
  `omissions`, `uniqueRectangle`, `yWing`, `xyzWing`, `xWing`, `swordfish`, `jellyfish`
) SELECT `id`, `puzzleData`, `clueCount`, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 FROM `$rename`;";
			echo "$sql\n\n";
		}
	}

	if ($mode === 1) {
		$logic = "`solveType`=0 AND `omissionSimple`=0 AND `naked2Simple`=0 AND `naked3Simple`=0 AND `nakedSimple`=0";
		$sql = tableStatement($tableCount, "clueCount", "simple_hidden", $logic);
		echo "$sql\n";

		$logic = "`solveType`=0 AND `omissionSimple`>0 AND `naked2Simple`=0 AND `naked3Simple`=0 AND `nakedSimple`=0";
		$sql = tableStatement($tableCount, "omissionSimple", "simple_omission", $logic);
		echo "$sql\n";

		$logic = "`solveType`=0 AND `naked2Simple`>0 AND `naked3Simple`=0 AND `nakedSimple`=0";
		$sql = tableStatement($tableCount, "naked2Simple", "simple_naked2", $logic);
		echo "$sql\n";

		$logic = "`solveType`=0 AND `naked3Simple`>0 AND `nakedSimple`=0";
		$sql = tableStatement($tableCount, "naked3Simple", "simple_naked3", $logic);
		echo "$sql\n";

		$logic = "`solveType`=0 AND `nakedSimple`>0";
		$sql = tableStatement($tableCount, "nakedSimple", "simple_naked", $logic);
		echo "$sql\n";

		$logic = "`solveType`=1 AND `naked2Visible`>0 AND `naked3Visible`=0 AND `nakedVisible`=0";
		$sql = tableStatement($tableCount, "naked2Visible", "visible_naked2", $logic);
		echo "$sql\n";

		$logic = "`solveType`=1 AND `naked3Visible`>0 AND `nakedVisible`=0";
		$sql = tableStatement($tableCount, "naked3Visible", "visible_naked3", $logic);
		echo "$sql\n";

		$logic = "`solveType`=1 AND `nakedVisible`>0";
		$sql = tableStatement($tableCount, "nakedVisible", "visible_naked", $logic);
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
		$logic .= strategyLogic("naked3Simple");
		$logic .= strategyLogic("naked2Simple");
		$logic .= strategyLogic("nakedSimple");
		$logic .= strategyLogic("naked3Visible");
		$logic .= strategyLogic("naked2Visible");
		$logic .= strategyLogic("nakedVisible");
		$logic .= strategyLogic("omissionVisible");
		$logic .= tableLogic();
		$sql = tableStatement($tableCount, "clueCount", "unsolvable", $logic);
		echo "$sql\n";

		$logic = "`solveType`=4";
		$select = "(";
		$select .= "clueCount + hiddenSimple + omissionSimple + naked2Simple + naked3Simple + nakedSimple + nakedVisible";
		$select .= ") AS `filled`";
		echo tableStatement($tableCount, "filled", "unsolvable_filled", $logic, $select), "\n";

		// Show count vs max
		$unions = [];
		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$unions[] = "SELECT COUNT(`id`) AS count, Max(`id`) AS max FROM `$table`";
		}
		if (count($unions) === 1) {
			$unionString = $unions[0];
			$sql = "$unionString;";
		} else {
			$unionString = implode("\n UNION ALL\n ", $unions);
			$sql = "SELECT FORMAT(SUM(`count`),0) AS 'count', FORMAT(SUM(`max`),0) AS 'max' FROM (\n $unionString\n)";
			$sql .= " AS puzzles;";
		}
		echo "$sql\n";
	}

	if ($mode === 2) {
		echo "--- Populated Tables ", number_format($totalCount), "\n\n";

		$tableNames = [
			"simple_hidden",
			"simple_omission",
			"simple_naked2",
			"simple_naked3",
			"simple_naked",
			"visible_naked2",
			"visible_naked3",
			"visible_naked",
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
			"unsolvable_filled",
		];

		foreach ($tableNames as $tableName) {
			$sql = "SELECT COUNT(*) AS count, MAX(`count`) as max FROM `$tableName`";
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetch();
			$count = (int)$result['count'];
			$maxCount = (int)$result['max'];

			if ($count === 1000000) $percent = "Filled";
			else $percent = percentage($count, $totalCount, 5, 2);
			$max = number_format($maxCount);
			$format = number_format($count);
			echo "$tableName: $percent ($max) $format\n";
		}
		echo "\n";
	}

	if ($mode === 3) {
		$number = number_format($totalCount);
		flushOut("--- Total $number");

		$unions = [];
		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$unions[] = "SELECT `solveType`, COUNT(*) AS count FROM `$table` GROUP BY `solveType`";
		}
		if (count($unions) === 1) {
			$unionString = $unions[0];
			$sql = "$unionString;\n";
		} else {
			$unionString = implode("\n UNION ALL\n ", $unions);
			$sql = "SELECT `solveType`, SUM(`count`) AS count FROM\n($unionString\n)";
			$sql .= " AS puzzles GROUP BY `solveType`;\n";
		}

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

	if ($mode === 4) {
		$unions = [];
		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$sql = "SELECT ";
			$sql .= "`hiddenSimple`>0 AS hiddenSimple, MAX(`hiddenSimple`) AS hiddenSimpleMax, ";
			$sql .= "`omissionSimple`>0 AS omissionSimple, MAX(`omissionSimple`) AS omissionSimpleMax, ";
			$sql .= "`nakedSimple`>0 AS nakedSimple, MAX(`nakedSimple`) AS nakedSimpleMax, ";
			$sql .= "COUNT(*) AS count FROM `$table` WHERE `solveType`=0 ";
			$sql .= "GROUP BY hiddenSimple, omissionSimple, nakedSimple";
			$unions[] = $sql;
		}
		$unionString = implode(" UNION ALL ", $unions);
		$sql = "SELECT ";
		$sql .= "hiddenSimple, MAX(hiddenSimpleMax) AS hiddenSimpleMax, ";
		$sql .= "omissionSimple, MAX(omissionSimpleMax) AS omissionSimpleMax, ";
		$sql .= "nakedSimple, MAX(nakedSimpleMax) AS nakedSimpleMax, ";
		$sql .= "SUM(`count`) AS count FROM ($unionString) AS puzzles ";
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

	if ($mode === 5) {
		$unions = [];
		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$sql = "SELECT ";
			$sql .= "SUM(`hiddenSimple`>0) AS hiddenSimple, MAX(`hiddenSimple`) AS hiddenSimpleMax, ";
			$sql .= "SUM(`omissionSimple`>0) AS omissionSimple, MAX(`omissionSimple`) AS omissionSimpleMax, ";
			$sql .= "SUM(`nakedSimple`>0) AS nakedSimple, MAX(`nakedSimple`) AS nakedSimpleMax, ";
			$sql .= "SUM(`nakedVisible`>0) AS nakedVisible, MAX(`nakedVisible`) AS nakedVisibleMax, ";
			$sql .= "SUM(`omissionVisible`>0) AS omissionVisible, MAX(`omissionVisible`) AS omissionVisibleMax, ";
			$sql .= "COUNT(*) AS count FROM `$table` WHERE `solveType`=1";
			$unions[] = $sql;
		}
		$unionString = implode(" UNION ALL ", $unions);
		$sql = "SELECT ";
		$sql .= "SUM(hiddenSimple) AS hiddenSimple, MAX(hiddenSimpleMax) AS hiddenSimpleMax, ";
		$sql .= "SUM(omissionSimple) AS omissionSimple, MAX(omissionSimpleMax) AS omissionSimpleMax, ";
		$sql .= "SUM(nakedSimple) AS nakedSimple, MAX(nakedSimpleMax) AS nakedSimpleMax, ";
		$sql .= "SUM(nakedVisible) AS nakedVisible, MAX(nakedVisibleMax) AS nakedVisibleMax, ";
		$sql .= "SUM(omissionVisible) AS omissionVisible, MAX(omissionVisibleMax) AS omissionVisibleMax, ";
		$sql .= "SUM(count) AS count FROM ($unionString) AS puzzles";

		$stmt = $db->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetch(\PDO::FETCH_ASSOC);

		$total = (int)$result['count'];

		$percent = percentage($total, $totalCount, 2);
		$number = number_format($total);
		echo "--- Visuals $percent $number\n";

		$hiddenSimple = (int)$result['hiddenSimple'];
		$hiddenSimpleMax = (int)$result['hiddenSimpleMax'];
		$percent = percentage($hiddenSimple, $total, 2);
		$max = number_format($hiddenSimpleMax);
		$format = number_format($hiddenSimple);
		echo "Simple Hidden: $percent ($max) $format\n";

		$omissionSimple = (int)$result['omissionSimple'];
		$omissionSimpleMax = (int)$result['omissionSimpleMax'];
		$percent = percentage($omissionSimple, $total, 2);
		$max = number_format($omissionSimpleMax);
		$format = number_format($omissionSimple);
		echo "Simple Omission: $percent ($max) $format\n";

		$nakedSimple = (int)$result['nakedSimple'];
		$nakedSimpleMax = (int)$result['nakedSimpleMax'];
		$percent = percentage($nakedSimple, $total, 2);
		$max = number_format($nakedSimpleMax);
		$format = number_format($nakedSimple);
		echo "Simple Naked: $percent ($max) $format\n";

		$nakedVisible = (int)$result['nakedVisible'];
		$nakedVisibleMax = (int)$result['nakedVisibleMax'];
		$percent = percentage($nakedVisible, $total, 2);
		$max = number_format($nakedVisibleMax);
		$format = number_format($nakedVisible);
		echo "Naked: $percent ($max) $format\n";

		$omissionVisible = (int)$result['omissionVisible'];
		$omissionVisibleMax = (int)$result['omissionVisibleMax'];
		$percent = percentage($omissionVisible, $total, 2);
		$max = number_format($omissionVisibleMax);
		$format = number_format($omissionVisible);
		echo "Omission: $percent ($max) $format\n";

		echo "\n";
	}

	if ($mode === 5) {
		$strategies = [
			"naked2",
			"naked3",
			"naked4",
			"hidden1",
			"hidden2",
			"hidden3",
			"hidden4",
			"omissions",
			"uniqueRectangle",
			"yWing",
			"xyzWing",
			"xWing",
			"swordfish",
			"jellyfish",
		];
		$unions = [];
		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$sql = "SELECT ";
			foreach ($strategies as $strategy) {
				$isolated = tableLogic($strategy);
				$sql .= "SUM(`{$strategy}`>0) AS {$strategy}, ";
				$sql .= "MAX(`{$strategy}`) AS {$strategy}Max, ";
				$sql .= "SUM(`{$strategy}`>0 AND `solveType`=3) AS {$strategy}Min, ";
				$sql .= "MAX(IF(`solveType`=3, `{$strategy}`, 0)) AS {$strategy}MinMax, ";
				$sql .= "SUM(`solveType`=3$isolated) as {$strategy}Iso, ";
				$sql .= "MAX(IF(`solveType`=3$isolated, `{$strategy}`, 0)) as {$strategy}IsoMax, ";
			}
			$sql .= "COUNT(*) AS count FROM `$table` WHERE `solveType`=2 OR `solveType`=3";
			$unions[] = $sql;
		}
		$unionString = implode(" UNION ALL ", $unions);
		$sql = "SELECT ";
		foreach ($strategies as $strategy) {
			$sql .= "SUM({$strategy}) AS {$strategy}, ";
			$sql .= "MAX({$strategy}Max) AS {$strategy}Max, ";
			$sql .= "SUM({$strategy}Min) AS {$strategy}Min, ";
			$sql .= "MAX({$strategy}MinMax) AS {$strategy}MinMax, ";
			$sql .= "SUM({$strategy}Iso) AS {$strategy}Iso, ";
			$sql .= "MAX({$strategy}IsoMax) AS {$strategy}IsoMax, ";
		}
		$sql .= "SUM(count) AS count FROM ($unionString) AS puzzles";

		$stmt = $db->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetch(\PDO::FETCH_ASSOC);

		$total = (int)$result['count'];

		$percent = percentage($total, $totalCount, 2);
		$number = number_format($total);
		echo "--- Strategies $percent $number\n";

		echo "\n";
		echo str_pad("Strategy", 17, " ", STR_PAD_BOTH);
		echo str_pad("Percent (Max) Count", 34, " ", STR_PAD_BOTH);
		echo str_pad("Minimal", 34, " ", STR_PAD_BOTH);
		echo str_pad("Isolated", 34, " ", STR_PAD_BOTH);
		echo "\n";
		echo str_pad(str_pad("", 16, "-", STR_PAD_BOTH), 17, " ");
		echo str_pad(str_pad("", 33, "-", STR_PAD_BOTH), 34, " ");
		echo str_pad(str_pad("", 33, "-", STR_PAD_BOTH), 34, " ");
		echo str_pad(str_pad("", 33, "-", STR_PAD_BOTH), 34, " ");
		echo "\n";

		$strategyNames = [
			"Naked2",
			"Naked3",
			"Naked4",
			"Hidden1",
			"Hidden2",
			"Hidden3",
			"Hidden4",
			"Omissions",
			"Unique Rectangle",
			"Y-Wing",
			"XYZ-Wing",
			"X-Wing",
			"Swordfish",
			"Jellyfish",
		];
		$strategyCount = count($strategyNames);
		for ($i = 0; $i < $strategyCount; $i++) {
			$title = $strategyNames[$i];
			$strategy = $strategies[$i];

			$strategyType = (int)$result[$strategy];
			$strategyType_Max = (int)$result["{$strategy}Max"];
			$strategyType_Min = (int)$result["{$strategy}Min"];
			$strategyType_MinMax = (int)$result["{$strategy}MinMax"];
			$strategyType_Iso = (int)$result["{$strategy}Iso"];
			$strategyType_IsoMax = (int)$result["{$strategy}IsoMax"];

			$percent = percentage($strategyType, $total, 5);
			$max = number_format($strategyType_Max);
			$format = number_format($strategyType);

			$percentMin = percentage($strategyType_Min, $total, 5);
			$maxMin = number_format($strategyType_MinMax);
			$formatMin = number_format($strategyType_Min);

			$percentIso = percentage($strategyType_Iso, $total, 5);
			$maxIso = number_format($strategyType_IsoMax);
			$formatIso = number_format($strategyType_Iso);

			echo str_pad("{$title}", 17, " ");
			echo str_pad("$percent ($max) $format", 34, " ");
			echo str_pad("$percentMin ($maxMin) $formatMin", 34, " ");
			echo str_pad("$percentIso ($maxIso) $formatIso", 34, " ");
			echo "\n";
		}

		echo  "\n";
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
			$unions[] = "SELECT `clueCount`, `solveType`, COUNT(*) AS count FROM `$table` GROUP BY `clueCount`, `solveType`";
		}
		if (count($unions) === 1) {
			$unionString = $unions[0];
			$sql = "$unionString;\n";
		} else {
			$unionString = implode("\n UNION ALL\n ", $unions);
			$sql = "SELECT `clueCount`, `solveType`, SUM(`count`) AS count FROM\n($unionString\n)";
			$sql .= " AS puzzles GROUP BY `clueCount`, `solveType`;\n";
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