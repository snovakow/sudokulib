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

function tableStatement($tableCount, $select, $tableName, $logic)
{
	$tableName_tmp = "{$tableName}_tmp";

	$sql = "";
	$sql .= "DROP TABLE IF EXISTS `$tableName`;\n";
	$sql .= "CREATE TABLE `$tableName` (\n";
	$sql .= "  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,\n";
	$sql .= "  `count` tinyint(2) unsigned NOT NULL,\n";
	$sql .= "  `puzzle_id` int(10) unsigned NOT NULL,\n";
	$sql .= "  `table_id` int(10) unsigned NOT NULL,\n";
	$sql .= "  PRIMARY KEY (`id`)\n";
	$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=ascii;\n";

	if ($tableCount > 1) {
		$sql .= "DROP TEMPORARY TABLE IF EXISTS `$tableName_tmp`;\n";
		$sql .= "CREATE TEMPORARY TABLE `$tableName_tmp` (\n";
		$sql .= "  `count` tinyint(2) unsigned NOT NULL,\n";
		$sql .= "  `puzzle_id` int(10) unsigned NOT NULL,\n";
		$sql .= "  `table_id` int(10) unsigned NOT NULL\n";
		$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=ascii;\n";
	}

	for ($table_id = 1; $table_id <= $tableCount; $table_id++) {
		$tableLead = $tableName;
		$tableSwap = $tableName_tmp;
		if ($tableCount % 2 !=  $table_id % 2) {
			$tableLead = $tableName_tmp;
			$tableSwap = $tableName;
		}

		if ($table_id > 2) {
			$sql .= "TRUNCATE TABLE `$tableLead`;\n";
			if ($table_id == $tableCount) {
				$sql .= "ALTER TABLE `$tableLead` AUTO_INCREMENT=1;\n";
			}
		}

		$sql .= "INSERT INTO `$tableLead` (`count`, `puzzle_id`, `table_id`)\n";

		$table = tableName($table_id);

		$selectLogic = "SELECT $select AS count, `id` AS puzzle_id, '$table_id' AS table_id FROM `$table` WHERE $logic";
		if ($table_id > 1) {
			$sql .= "($selectLogic) \n";
			$sql .= "UNION ALL (SELECT `count`, `puzzle_id`, `table_id` FROM `$tableSwap`) \n";
		} else {
			$sql .= "$selectLogic \n";
		}
		$sql .= "ORDER BY `count` DESC LIMIT 1000000;\n";
	}

	if ($tableCount > 1) {
		$sql .= "DROP TEMPORARY TABLE `$tableName_tmp`;\n";
	}

	$sql .= "ALTER TABLE `$tableName` AUTO_INCREMENT=1;\n";
	return $sql;
}
function tableStrategyLogic($tableCount, $solveType, $strategy, $tableName)
{
	$logic = "`solveType`=$solveType";
	$logic .= tableLogic($strategy);
	$sql = tableStatement($tableCount, $strategy, $tableName, $logic);
	return "$sql\n";
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
  `naked2Visible` tinyint(3) unsigned NOT NULL DEFAULT '0',
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

			$sql = "INSERT INTO `$tableName` (`id`, `puzzleData`, `clueCount`, `solveType`) SELECT `id`, `puzzleData`, `clueCount`, 5 FROM `$rename`;";
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

		$logic = "`solveType`=1";
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
		$logic .= strategyLogic("naked2Simple");
		$logic .= strategyLogic("naked3Simple");
		$logic .= strategyLogic("nakedSimple");
		$logic .= strategyLogic("omissionVisible");
		$logic .= strategyLogic("naked2Visible");
		$logic .= strategyLogic("nakedVisible");
		$logic .= tableLogic();
		$sql = tableStatement($tableCount, "clueCount", "unsolvable", $logic);
		echo "$sql\n";

		$logic = "`solveType`=4";
		$select = "(clueCount + hiddenSimple + omissionSimple + naked2Simple + naked3Simple + nakedSimple + nakedVisible)";
		echo tableStatement($tableCount, $select, "unsolvable_filled", $logic), "\n";

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

		printf("%-26s%8s%4s%10s\n", "Table", "Percent", "Max", "Count");
		printf("%'-26s%'-9s%'-4s%'-10s\n", " ", " ", " ", " ");
		foreach ($tableNames as $tableName) {
			$sql = "SELECT COUNT(*) AS count, MAX(`count`) as max FROM `$tableName`";
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetch();
			$count = (int)$result['count'];
			$maxCount = (int)$result['max'];

			$percent = percentage($count, 1000000, 3, 2);
			$max = number_format($maxCount);
			$format = number_format($count);
			printf("%-26s%8s%4s%10s\n", $tableName, $percent, $max, $format);
		}
		echo "\n";
	}

	if ($mode === 3) {
		$number = number_format($totalCount);
		echo "--- Total $number\n\n";

		$counts = [];
		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$sql = "SELECT `solveType`, COUNT(*) AS count FROM `$table` GROUP BY `solveType`";
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			foreach ($result as $key => $row) {
				$solveType = (int)$row['solveType'];
				$count = (int)$row['count'];
				if (!array_key_exists($solveType, $counts)) $counts[$solveType] = $count;
				else $counts[$solveType] += $count;
			}
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
		$hiddenSimple = 0;
		$hiddenSimpleIso = 0;
		$hiddenSimpleIsoMin = 81;
		$hiddenSimpleIsoMax = 0;

		$omissionSimple = 0;
		$omissionSimpleIso = 0;
		$omissionSimpleIsoMax = 0;

		$naked2Simple = 0;
		$naked2SimpleIso = 0;
		$naked2SimpleIsoMax = 0;

		$naked3Simple = 0;
		$naked3SimpleIso = 0;
		$naked3SimpleIsoMax = 0;

		$nakedSimple = 0;
		$nakedSimpleIso = 0;
		$nakedSimpleIsoMax = 0;

		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$sql = "SELECT ";
			$sql .= "SUM(`hiddenSimple`>0) AS hiddenSimple ";
			$sql .= ", SUM(`solveType`=0 AND `hiddenSimple`>0 AND `omissionSimple`=0 AND `naked2Simple`=0 AND `naked3Simple`=0 AND `nakedSimple`=0) AS hiddenSimpleIso ";
			$sql .= ", MAX((`solveType`=0) * (81 - `hiddenSimple`) * (`hiddenSimple`>0) * (`omissionSimple`=0) * (`naked2Simple`=0) * (`naked3Simple`=0) * (`nakedSimple`=0)) AS hiddenSimpleIsoMin ";
			$sql .= ", MAX((`solveType`=0) * (`hiddenSimple`) * (`omissionSimple`=0) * (`naked2Simple`=0) * (`naked3Simple`=0) * (`nakedSimple`=0)) AS hiddenSimpleIsoMax ";

			$sql .= ", SUM(`omissionSimple`>0) AS omissionSimple ";
			$sql .= ", SUM(`solveType`=0 AND `omissionSimple`>0 AND `naked2Simple`=0 AND `naked3Simple`=0 AND `nakedSimple`=0) AS omissionSimpleIso ";
			$sql .= ", MAX((`solveType`=0) * (`omissionSimple`) * (`naked2Simple`=0) * (`naked3Simple`=0) * (`nakedSimple`=0)) AS omissionSimpleIsoMax ";

			$sql .= ", SUM(`naked2Simple`>0) AS naked2Simple ";
			$sql .= ", SUM(`solveType`=0 AND `naked2Simple`>0 AND `naked3Simple`=0 AND `nakedSimple`=0) AS naked2SimpleIso ";
			$sql .= ", MAX((`solveType`=0) * (`naked2Simple`) * (`naked3Simple`=0) * (`nakedSimple`=0)) AS naked2SimpleIsoMax ";

			$sql .= ", SUM(`naked3Simple`>0) AS naked3Simple ";
			$sql .= ", SUM(`solveType`=0 AND `naked3Simple`>0 AND `nakedSimple`=0) AS naked3SimpleIso ";
			$sql .= ", MAX((`solveType`=0) * (`naked3Simple`) * (`nakedSimple`=0)) AS naked3SimpleIsoMax ";

			$sql .= ", SUM(`nakedSimple`>0) AS nakedSimple ";
			$sql .= ", SUM(`solveType`=0 AND `nakedSimple`>0) AS nakedSimpleIso ";
			$sql .= ", MAX((`solveType`=0) * (`nakedSimple`) * (`nakedSimple`=0)) AS nakedSimpleIsoMax ";

			$sql .= ", SUM(`nakedSimple`>0) AS nakedSimple ";
			$sql .= ", SUM(`solveType`=0 AND `nakedSimple`>0) AS nakedSimpleIso ";
			$sql .= ", MAX((`solveType`=1) * (`nakedSimple`) * (`nakedSimple`=0)) AS nakedSimpleIsoMax ";

			$sql .= "FROM `$table`";

			$stmt = $db->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetch(\PDO::FETCH_ASSOC);

			$hiddenSimple += (int)$result['hiddenSimple'];
			$hiddenSimpleIso += (int)$result['hiddenSimpleIso'];
			$hiddenSimpleIsoMin = min($hiddenSimpleIsoMin, 81 - (int)$result['hiddenSimpleIsoMin']);
			$hiddenSimpleIsoMax = max($hiddenSimpleIsoMax, (int)$result['hiddenSimpleIsoMax']);

			$omissionSimple += (int)$result['omissionSimple'];
			$omissionSimpleIso += (int)$result['omissionSimpleIso'];
			$omissionSimpleIsoMax += max($omissionSimpleIsoMax, (int)$result['omissionSimpleIsoMax']);

			$naked2Simple += (int)$result['naked2Simple'];
			$naked2SimpleIso += (int)$result['naked2SimpleIso'];
			$naked2SimpleIsoMax += max($naked2SimpleIsoMax, (int)$result['naked2SimpleIsoMax']);

			$naked3Simple += (int)$result['naked3Simple'];
			$naked3SimpleIso += (int)$result['naked3SimpleIso'];
			$naked3SimpleIsoMax += max($naked3SimpleIsoMax, (int)$result['naked3SimpleIsoMax']);

			$nakedSimple += (int)$result['nakedSimple'];
			$nakedSimpleIso += (int)$result['nakedSimpleIso'];
			$nakedSimpleIsoMax += max($nakedSimpleIsoMax, (int)$result['nakedSimpleIsoMax']);
		}

		$number = number_format($totalCount);
		echo "Total: $number\n";

		$runningTotoal = 0;
		$runningTotoal += $hiddenSimpleIso;

		$percent = percentage($hiddenSimple, $totalCount, 2);
		$format = number_format($hiddenSimple);
		echo "Simple Hidden: $percent $format\n";
		$percent = percentage($hiddenSimpleIso, $totalCount, 2);
		$min = number_format(81 - $hiddenSimpleIsoMin);
		$max = number_format(81 - $hiddenSimpleIsoMax);
		$format = number_format($hiddenSimpleIso);
		echo "Simple Hidden Iso: $percent ($max $min) $format\n";

		$runningTotoal += $omissionSimpleIso;
		$runningPercent = percentage($runningTotoal, $totalCount, 2);

		$percent = percentage($omissionSimple, $totalCount, 2);
		$format = number_format($omissionSimple);
		echo "Simple Omission: $percent $format\n";
		$percent = percentage($omissionSimpleIso, $totalCount, 2);
		$max = number_format($omissionSimpleIsoMax);
		$format = number_format($omissionSimpleIso);
		echo "Simple Omission Iso: $runningPercent ($percent $max) $format\n";

		$runningTotoal += $naked2SimpleIso;
		$runningPercent = percentage($runningTotoal, $totalCount, 2);

		$percent = percentage($naked2Simple, $totalCount, 2);
		$format = number_format($naked2Simple);
		echo "Simple Naked2: $percent $format\n";
		$percent = percentage($naked2SimpleIso, $totalCount, 2);
		$max = number_format($naked2SimpleIsoMax);
		$format = number_format($naked2SimpleIso);
		echo "Simple Naked2 Iso: $runningPercent ($percent $max) $format\n";

		$runningTotoal += $naked3SimpleIso;
		$runningPercent = percentage($runningTotoal, $totalCount, 2);

		$percent = percentage($naked3Simple, $totalCount, 2);
		$format = number_format($naked3Simple);
		echo "Simple Naked3: $percent $format\n";
		$percent = percentage($naked3SimpleIso, $totalCount, 2);
		$max = number_format($naked3SimpleIsoMax);
		$format = number_format($naked3SimpleIso);
		echo "Simple Naked3 Iso: $runningPercent ($percent $max) $format\n";

		$runningTotoal += $nakedSimpleIso;
		$runningPercent = percentage($runningTotoal, $totalCount, 2);

		$percent = percentage($nakedSimple, $totalCount, 2);
		$format = number_format($nakedSimple);
		echo "Simple Naked: $percent $format\n";
		$percent = percentage($nakedSimpleIso, $totalCount, 2);
		$max = number_format($nakedSimpleIsoMax);
		$format = number_format($nakedSimpleIso);
		echo "Simple Naked Iso: $runningPercent ($percent $max) $format\n";

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
		echo "--- Clues\n";
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