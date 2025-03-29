<?php

const MAX_SIZE = 10000000;

// 0 = Update Prep Statements
// 1 = Populate Statements
// 2 = Populated Tables
// 3 = Totals
// 4 = Visuals
// 5 = Strategies
// 6 = Clues

if (!isset($_GET['mode'])) die;

$mode = (int)$_GET['mode'];
if (!is_int($mode) || $mode < 0 || $mode > 6) die;

function totalCount($tableCount, $puzzleCount)
{
	if ($tableCount === 0) return 0;
	return (($tableCount - 1) * MAX_SIZE) +  $puzzleCount;
}

function tableName($number, $append = "")
{
	$pad = str_pad($number, 3, "0", STR_PAD_LEFT);
	$puzzles = "puzzles";
	return "$puzzles$append$pad";
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
	echo "$stat\n";
}

function queryStrategy($db, $table)
{
	$stmt = $db->prepare("SELECT COUNT(*) AS count, MAX(`count`) AS max FROM `" . $table . "`");
	$stmt->execute();
	$result = $stmt->fetch();
	return $result;
}

function tableGeneralStatement($tableCount, $tableName, $fields, $select, $logic, $order)
{
	$tableName_offline = "{$tableName}_offline";
	$tableName_tmp1 = "{$tableName}_tmp1";
	$tableName_tmp2 = "{$tableName}_tmp2";
	$sql = "";

	$sql .= "DROP TABLE IF EXISTS `$tableName_offline`;\n";
	$sql .= "CREATE TABLE `$tableName_offline` (\n";
	$sql .= "  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,\n";
	foreach ($fields as $field) $sql .= "  `$field` tinyint(2) unsigned NOT NULL,\n";
	$sql .= "  `puzzle_id` int(10) unsigned NOT NULL,\n";
	$sql .= "  `table_id` int(10) unsigned NOT NULL,\n";
	$sql .= "  PRIMARY KEY (`id`)\n";
	$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=ascii;\n";

	if ($tableCount > 1) {
		$sql .= "DROP TEMPORARY TABLE IF EXISTS `$tableName_tmp1`;\n";
		$sql .= "CREATE TEMPORARY TABLE `$tableName_tmp1` (\n";
		foreach ($fields as $field) $sql .= "  `$field` tinyint(2) unsigned NOT NULL,\n";
		$sql .= "  `puzzle_id` int(10) unsigned NOT NULL,\n";
		$sql .= "  `table_id` int(10) unsigned NOT NULL\n";
		$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=ascii;\n";
	}

	if ($tableCount > 2) {
		$sql .= "DROP TEMPORARY TABLE IF EXISTS `$tableName_tmp2`;\n";
		$sql .= "CREATE TEMPORARY TABLE `$tableName_tmp2` (\n";
		foreach ($fields as $field) $sql .= "  `$field` tinyint(2) unsigned NOT NULL,\n";
		$sql .= "  `puzzle_id` int(10) unsigned NOT NULL,\n";
		$sql .= "  `table_id` int(10) unsigned NOT NULL\n";
		$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=ascii;\n";
	}

	$tableLead = $tableName_offline;
	for ($table_id = 1; $table_id <= $tableCount; $table_id++) {
		$tableSwap = $tableLead;
		if ($table_id == $tableCount) $tableLead = $tableName_offline;
		else $tableLead = ($table_id % 2 == 1) ? $tableName_tmp1 : $tableName_tmp2;

		if ($table_id > 2 && $table_id < $tableCount) $sql .= "TRUNCATE TABLE `$tableLead`;\n";

		$fieldList = implode("`, `", $fields);
		$sql .= "INSERT INTO `$tableLead` (`$fieldList`, `puzzle_id`, `table_id`)\n";

		$table = tableName($table_id);

		$selectLogic = "SELECT $select, `id` AS puzzle_id, '$table_id' AS table_id FROM `$table` WHERE $logic";
		if ($table_id > 1) {
			$sql .= "($selectLogic) \n";
			$sql .= "UNION ALL (SELECT `$fieldList`, `puzzle_id`, `table_id` FROM `$tableSwap`) \n";
		} else {
			$sql .= "$selectLogic \n";
		}
		$sql .= "ORDER BY {$order} LIMIT 1000000;\n";
	}

	$sql .= "DROP TABLE IF EXISTS `$tableName`;\n";
	$sql .= "RENAME TABLE $tableName_offline TO $tableName;\n";

	if ($tableCount > 1) $sql .= "DROP TEMPORARY TABLE `$tableName_tmp1`;\n";
	if ($tableCount > 2) $sql .= "DROP TEMPORARY TABLE `$tableName_tmp2`;\n";

	return $sql;
}
function tableStatement($tableCount, $select, $tableName, $logic, $min = false)
{

	$fields = ["count", "clueCount"];
	$select = "$select AS count, `clueCount`";
	$order = $min ? "count, clueCount DESC" : "count DESC";
	return tableGeneralStatement($tableCount, $tableName, $fields, $select, $logic, $order);
}
function tableStrategyLogic($tableCount, $strategy, $tableName, $frequency = 1, $min = false)
{
	$logic = "`solveType`=5";
	$logic .= tableLogic($strategy, $frequency);
	$sql = tableStatement($tableCount, $strategy, $tableName, $logic, $min);
	return "$sql\n";
}

function strategyLogic($strategy, $priority = "", $frequency = 1)
{
	if ($strategy == $priority) return " AND `$strategy`>=$frequency";
	return " AND `$strategy`=0";
}
function tableLogic($strategy = "", $frequency = 1)
{
	$logic = "";
	$logic .= strategyLogic("naked2", $strategy, $frequency);
	$logic .= strategyLogic("naked3", $strategy, $frequency);
	$logic .= strategyLogic("naked4", $strategy, $frequency);
	$logic .= strategyLogic("hidden1", $strategy, $frequency);
	$logic .= strategyLogic("hidden2", $strategy, $frequency);
	$logic .= strategyLogic("hidden3", $strategy, $frequency);
	$logic .= strategyLogic("hidden4", $strategy, $frequency);
	$logic .= strategyLogic("omissions", $strategy, $frequency);
	$logic .= strategyLogic("uniqueRectangle", $strategy, $frequency);
	$logic .= strategyLogic("yWing", $strategy, $frequency);
	$logic .= strategyLogic("xyzWing", $strategy, $frequency);
	$logic .= strategyLogic("xWing", $strategy, $frequency);
	$logic .= strategyLogic("swordfish", $strategy, $frequency);
	$logic .= strategyLogic("jellyfish", $strategy, $frequency);
	return $logic;
}

try {
	$servername = "localhost";
	$username = "snovakow";
	$password = "kewbac-recge1-Fiwpux";
	$dbname = "sudoku";
	$db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

	$tableCount = 0;
	$puzzleCount = 0;
	$totalCount = 0;

	$tables = "tables";

	$tableCount = $_GET['table'];
	if (is_int($tableCount) && $tableCount >= 1 && $tableCount <= 1000) {
		$puzzleCount = MAX_SIZE;
		$totalCount = totalCount($tableCount, $puzzleCount);
	} else {
		$stmt = $db->prepare("SELECT `tableCount`, `puzzleCount` FROM `$tables`");
		$stmt->execute();
		$result = $stmt->fetch();
		$tableCount = $result['tableCount'];
		$puzzleCount = $result['puzzleCount'];
		$totalCount = totalCount($tableCount, $puzzleCount);
	}

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
  `clueCount` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `solveType` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `hiddenSimple` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `nakedSimple` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `omissionSimple` tinyint(3) unsigned NOT NULL DEFAULT '0',
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

			$sql = "INSERT INTO `$tableName` (`id`, `puzzleData`, `clueCount`) SELECT `id`, `puzzleData`, `clueCount` FROM `$rename`;";
			echo "$sql\n";
		}
	}

	if ($mode === 1) {
		echo tableStrategyLogic($tableCount, "naked2", "candidate_naked2");
		echo tableStrategyLogic($tableCount, "naked3", "candidate_naked3");
		echo tableStrategyLogic($tableCount, "naked4", "candidate_naked4");
		echo tableStrategyLogic($tableCount, "hidden1", "candidate_hidden1");
		echo tableStrategyLogic($tableCount, "hidden2", "candidate_hidden2");
		echo tableStrategyLogic($tableCount, "hidden3", "candidate_hidden3");
		echo tableStrategyLogic($tableCount, "hidden4", "candidate_hidden4");
		echo tableStrategyLogic($tableCount, "omissions", "candidate_omissions");
		echo tableStrategyLogic($tableCount, "uniqueRectangle", "candidate_uniqueRectangle");
		echo tableStrategyLogic($tableCount, "yWing", "candidate_yWing");
		echo tableStrategyLogic($tableCount, "xyzWing", "candidate_xyzWing");
		echo tableStrategyLogic($tableCount, "xWing", "candidate_xWing");
		echo tableStrategyLogic($tableCount, "swordfish", "candidate_swordfish");
		echo tableStrategyLogic($tableCount, "jellyfish", "candidate_jellyfish");

		$unions = [];
		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$unions[] = "SELECT $i AS `Table`, COUNT(*) AS `Count` FROM `$table` WHERE `hiddenSimple`=0 AND `solveType`=0";
		}
		$select = implode(" UNION ALL ", $unions);
		$sql = "SELECT `Table` AS `Incomplete`, `Count` FROM ($select) AS unions WHERE `Count`>0;";
		echo "$sql\n";
	}

	if ($mode === 2) {
		echo "--- Populated Tables ", number_format($totalCount), "\n\n";

		$tableNames = [
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

		$len1 = 25;
		$len2 = 8;
		$len3 = 3;
		$len4 = 3;
		$len5 = 9;
		printf("%-{$len1}s %{$len2}s %{$len3}s %{$len4}s %{$len5}s\n", "Table", "Percent", "Min", "Max", "Count");
		printf("%'-{$len1}s %'-{$len2}s %'-{$len3}s %'-{$len4}s %'-{$len5}s\n", "", "", "", "", "");
		foreach ($tableNames as $tableName) {
			$property = "count";
			$sql = "SELECT COUNT(*) AS count, MIN(`$property`) AS min, MAX(`$property`) AS max FROM `$tableName`";

			$stmt = $db->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetch();
			$count = $result['count'];

			printf(
				"%-{$len1}s %{$len2}s %{$len3}s %{$len4}s %{$len5}s\n",
				$tableName,
				percentage($count, 1000000, 3, 2),
				number_format($result['min']),
				number_format($result['max']),
				number_format($count)
			);
		}
	}

	if ($mode === 3) {
		$counts = [];
		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$sql = "SELECT `solveType`, COUNT(*) AS count FROM `$table` GROUP BY `solveType`";
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			foreach ($result as $row) {
				$solveType = $row['solveType'];
				$count = $row['count'];
				if (array_key_exists($solveType, $counts)) $counts[$solveType] += $count;
				else $counts[$solveType] = $count;
			}
		}

		$hiddenSimple = $counts[0];
		$nakedSimple = $counts[1];
		$omissionSimple = $counts[2];
		$omissionVisible = $counts[3];
		$candidate = $counts[4];
		$candidateMin = $counts[5];
		$unsolvable = $counts[6];

		$results = [
			'hiddenSimple' => $hiddenSimple,
			'nakedSimple' => $nakedSimple,
			'omissionSimple' => $omissionSimple,
			'omissionVisible' => $omissionVisible,
			'candidate' => $candidate,
			'candidateMin' => $candidateMin,
			'unsolvable' => $unsolvable,
			'totalCount' => $totalCount
		];
		exit(json_encode($results));
	}

	if ($mode === 4) {
		$strategies = [
			"hiddenSimple",
			"nakedSimple",
			"omissionSimple",
		];

		$results = [];
		foreach ($strategies as $strategy) {
			if ($strategy == "hiddenSimple") {
				$results[$strategy] = ['min' => 82, 'max' => 0, 'count' => 0];
			} elseif ($strategy == "omissionSimple") {
				$results[$strategy] = ['maxNaked' => 0, 'maxPointer' => 0, 'count' => 0];
			} else {
				$results[$strategy] = ['max' => 0, 'count' => 0];
			}
		}

		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);

			foreach ($strategies as $strategy) {
				if ($strategy == 'hiddenSimple') {
					$sql = "SELECT COUNT(*) AS count, MIN(`clueCount`) AS min, MAX(`clueCount`) AS max FROM `$table` ";
					$sql .= "WHERE `solveType`=0";

					$stmt = $db->prepare($sql);
					$stmt->execute();
					$row = $stmt->fetch(\PDO::FETCH_ASSOC);

					$result = &$results[$strategy];
					$result['count'] += $row['count'];
					$result['min'] = min($row['min'], $result['min']);
					$result['max'] = max($row['max'], $result['max']);
				} elseif ($strategy == 'omissionSimple') {
					$sql = "SELECT COUNT(*) AS count, MAX(`nakedSimple`) AS maxNaked, ";
					$sql .= "MAX(`omissionSimple`) AS maxPointer ";
					$sql .= "FROM `$table` WHERE `solveType`=2";

					$stmt = $db->prepare($sql);
					$stmt->execute();
					$row = $stmt->fetch(\PDO::FETCH_ASSOC);

					$result = &$results[$strategy];
					$result['count'] += $row['count'];
					$result['maxNaked'] = max($row['maxNaked'], $result['maxNaked']);
					$result['maxPointer'] = max($row['maxPointer'], $result['maxPointer']);
				} else {
					$sql = "SELECT COUNT(*) AS count, MAX(`$strategy`) AS max FROM `$table` ";
					$sql .= "WHERE `solveType`=1";

					$stmt = $db->prepare($sql);
					$stmt->execute();
					$row = $stmt->fetch(\PDO::FETCH_ASSOC);

					$result = &$results[$strategy];
					$result['count'] += $row['count'];
					$result['max'] = max($row['max'], $result['max']);
				}
			}
		}

		$number = number_format($totalCount);
		echo "--- Total $number\n\n";

		$len_1 = 13;
		$len_2 = 10;
		$len_3 = 3;
		$len_4 = 11;

		echo str_pad("Strategy", $len_1, " ", STR_PAD_RIGHT);
		echo str_pad("%", $len_2 + 1, " ", STR_PAD_LEFT);
		echo str_pad("Max", $len_3 + 1, " ", STR_PAD_LEFT);
		echo str_pad("Count", $len_4 + 1, " ", STR_PAD_LEFT);
		echo "\n";
		echo str_pad("", $len_1, "-", STR_PAD_RIGHT);
		echo str_pad(str_pad("", $len_2, "-", STR_PAD_RIGHT), $len_2 + 1, " ", STR_PAD_LEFT);
		echo str_pad(str_pad("", $len_3, "-", STR_PAD_RIGHT), $len_3 + 1, " ", STR_PAD_LEFT);
		echo str_pad(str_pad("", $len_4, "-", STR_PAD_RIGHT), $len_4 + 1, " ", STR_PAD_LEFT);
		echo "\n";

		foreach ($strategies as $strategy) {
			$result = &$results[$strategy];
			if ($strategy == 'hiddenSimple') $title = "Hidden Min"; // $title = "Hidden Max";			
			if ($strategy == 'nakedSimple') $title = "Naked";
			if ($strategy == 'omissionSimple') $title = "Pointer";

			$percent = percentage($result['count'], $totalCount, 5);
			$number = number_format($result['count']);
			echo str_pad("$title", $len_1, " ", STR_PAD_RIGHT);
			echo str_pad("$percent", $len_2 + 1, " ", STR_PAD_LEFT);

			if ($strategy == 'hiddenSimple' || $strategy == 'nakedSimple') {
				if ($strategy == 'hiddenSimple') {
					$min = number_format($result['min']);

					echo str_pad("$min", $len_3 + 1, " ", STR_PAD_LEFT);
					echo str_pad("$number", $len_4 + 1, " ", STR_PAD_LEFT);
					echo "\n";

					$title = "Hidden Max";
					echo str_pad("$title", $len_1, " ", STR_PAD_RIGHT);
					echo str_pad("$percent", $len_2 + 1, " ", STR_PAD_LEFT);
				}
				$max = number_format($result['max']);

				echo str_pad("$max", $len_3 + 1, " ", STR_PAD_LEFT);
				echo str_pad("$number", $len_4 + 1, " ", STR_PAD_LEFT);
				echo "\n";
			}
			if ($strategy == 'omissionSimple') {
				$max = number_format($result['maxPointer']);

				echo str_pad("$max", $len_3 + 1, " ", STR_PAD_LEFT);
				echo str_pad("$number", $len_4 + 1, " ", STR_PAD_LEFT);
				echo "\n";

				$title = "Naked Pointer";
				$max = number_format($result['maxNaked']);

				echo str_pad("$title", $len_1, " ", STR_PAD_RIGHT);
				echo str_pad("$percent", $len_2 + 1, " ", STR_PAD_LEFT);
				echo str_pad("$max", $len_3 + 1, " ", STR_PAD_LEFT);
				echo str_pad("$number", $len_4 + 1, " ", STR_PAD_LEFT);
				echo "\n";
			}
		}
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

		$results = [];
		$total = 0;
		$minimal = 0;

		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);

			$sql = "SELECT ";
			foreach ($strategies as $strategy) {
				$isolated = tableLogic($strategy);
				$sql .= "SUM(`{$strategy}`>0) AS {$strategy}, ";
				$sql .= "MAX(`{$strategy}`) AS {$strategy}Max, ";
				$sql .= "SUM(`{$strategy}`>0 AND `solveType`=5) AS {$strategy}Min, ";
				$sql .= "MAX(IF(`solveType`=5, `{$strategy}`, 0)) AS {$strategy}MinMax, ";
				$sql .= "SUM(`solveType`=5$isolated) as {$strategy}Iso, ";
				$sql .= "MAX(IF(`solveType`=5$isolated, `{$strategy}`, 0)) as {$strategy}IsoMax, ";
			}
			$sql .= "COUNT(*) AS count FROM `$table` WHERE `solveType`=4 || `solveType`=5";

			$stmt = $db->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetch(\PDO::FETCH_ASSOC);

			$results[] = $result;
			$total += $result['count'];
		}

		$percent = percentage($total, $totalCount, 2);
		$number = number_format($total);
		echo "--- Strategies $percent $number\n\n";

		echo str_pad("Strategy", 17, " ", STR_PAD_RIGHT);
		echo str_pad("Percent (Max) Count", 27, " ", STR_PAD_RIGHT);
		echo str_pad("Minimal", 27, " ", STR_PAD_RIGHT);
		echo str_pad("Isolated", 27, " ", STR_PAD_RIGHT);
		echo "\n";
		echo str_pad(str_pad("", 16, "-", STR_PAD_RIGHT), 17, " ");
		echo str_pad(str_pad("", 26, "-", STR_PAD_RIGHT), 27, " ");
		echo str_pad(str_pad("", 26, "-", STR_PAD_RIGHT), 27, " ");
		echo str_pad(str_pad("", 26, "-", STR_PAD_RIGHT), 27, " ");
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

			$strategyType = 0;
			$strategyType_Max = 0;
			$strategyType_Min = 0;
			$strategyType_MinMax = 0;
			$strategyType_Iso = 0;
			$strategyType_IsoMax = 0;

			foreach ($results as $result) {
				$strategyType += $result[$strategy];
				$strategyType_Max = max($strategyType_Max, $result["{$strategy}Max"]);
				$strategyType_Min += $result["{$strategy}Min"];
				$strategyType_MinMax = max($strategyType_MinMax, $result["{$strategy}MinMax"]);
				$strategyType_Iso += $result["{$strategy}Iso"];
				$strategyType_IsoMax = max($strategyType_IsoMax, $result["{$strategy}IsoMax"]);
			}

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
			echo str_pad("$percent ($max) $format", 27, " ");
			echo str_pad("$percentMin ($maxMin) $formatMin", 27, " ");
			echo str_pad("$percentIso ($maxIso) $formatIso", 27, " ");
			echo "\n";
		}
	}

	if ($mode === 6) {
		$counts = [];
		$countTypes = [];

		for ($i = 1; $i <= $tableCount; $i++) {
			$table = tableName($i);
			$sql = "SELECT `clueCount`, `solveType`, COUNT(*) AS count FROM `$table` GROUP BY `clueCount`, `solveType`";
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			foreach ($result as $row) {
				$clueCount = $row['clueCount'];
				$solveType = $row['solveType'];
				$count = $row['count'];

				if (!$counts[$clueCount]) $counts[$clueCount] = 0;
				$counts[$clueCount] += $count;

				if (!$countTypes[$solveType]) $countTypes[$solveType] = [];

				if (!$countTypes[$solveType][$clueCount]) $countTypes[$solveType][$clueCount] = 0;
				$countTypes[$solveType][$clueCount] += $count;
			}
		}
		$results = [];
		$results['counts'] = $counts;
		$results['countTypes'] = $countTypes;
		$results['totalCount'] = $totalCount;

		exit(json_encode($results));
	}
} catch (PDOException $e) {
	// echo "Error: " . $e->getMessage();
}
