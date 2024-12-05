<!doctype html>
<html>

<head>
	<title>Stats</title>
</head>

<body>
	<pre>
<?php

class SimpleCounter
{
	public $nakedSimple;
	public $hiddenSimple;
	public $omissionSimple;
	public $count;

	function __construct()
	{
		$this->nakedSimple = 0;
		$this->hiddenSimple = 0;
		$this->omissionSimple = 0;
		$this->count = 0;
	}

	function addData($data)
	{
		$this->nakedSimple += $data->nakedSimple > 0 ? 1 : 0;
		$this->hiddenSimple += $data->hiddenSimple > 0 ? 1 : 0;
		$this->omissionSimple += $data->omissionSimple > 0 ? 1 : 0;
		$this->count++;
	}
}

class SimpleIsolatedCounter
{
	public $hiddenSimple;
	public $omissionSimple;
	public $nakedSimple;
	public $allSimple;
	public $count;

	function __construct()
	{
		$this->hiddenSimple = 0;
		$this->omissionSimple = 0;
		$this->nakedSimple = 0;
		$this->allSimple = 0;
		$this->count = 0;
	}

	function addData($data)
	{
		if ($data->omissionSimple === 0) {
			if ($data->nakedSimple === 0) $this->hiddenSimple++;
			else $this->nakedSimple++;
		} else {
			if ($data->nakedSimple === 0) $this->omissionSimple++;
			else $this->allSimple++;
		}
		$this->count++;
	}
}

class VisibleCounter extends SimpleCounter
{
	public $nakedVisible;
	public $omissionVisible;

	function __construct()
	{
		parent::__construct();
		$this->nakedVisible = 0;
		$this->omissionVisible = 0;
	}

	function addData($data)
	{
		parent::addData($data);
		$this->nakedVisible += $data->nakedVisible > 0 ? 1 : 0;
		$this->omissionVisible += $data->omissionVisible > 0 ? 1 : 0;
	}
}

class CandidateCounter extends VisibleCounter
{
	public $naked2;
	public $naked3;
	public $naked4;
	public $hidden1;
	public $hidden2;
	public $hidden3;
	public $hidden4;
	public $omissions;
	public $uniqueRectangle;
	public $yWing;
	public $xyzWing;
	public $xWing;
	public $swordfish;
	public $jellyfish;

	function __construct()
	{
		parent::__construct();
		$this->naked2 = 0;
		$this->naked3 = 0;
		$this->naked4 = 0;
		$this->hidden1 = 0;
		$this->hidden2 = 0;
		$this->hidden3 = 0;
		$this->hidden4 = 0;
		$this->omissions = 0;
		$this->uniqueRectangle = 0;
		$this->yWing = 0;
		$this->xyzWing = 0;
		$this->xWing = 0;
		$this->swordfish = 0;
		$this->jellyfish = 0;
	}

	function addData($data)
	{
		parent::addData($data);
		$this->naked2 += $data->naked2 > 0 ? 1 : 0;
		$this->naked3 += $data->naked3 > 0 ? 1 : 0;
		$this->naked4 += $data->naked4 > 0 ? 1 : 0;
		$this->hidden1 += $data->hidden1 > 0 ? 1 : 0;
		$this->hidden2 += $data->hidden2 > 0 ? 1 : 0;
		$this->hidden3 += $data->hidden3 > 0 ? 1 : 0;
		$this->hidden4 += $data->hidden4 > 0 ? 1 : 0;
		$this->omissions += $data->omissions > 0 ? 1 : 0;
		$this->uniqueRectangle += $data->uniqueRectangle > 0 ? 1 : 0;
		$this->yWing += $data->yWing > 0 ? 1 : 0;
		$this->xyzWing += $data->xyzWing > 0 ? 1 : 0;
		$this->xWing += $data->xWing > 0 ? 1 : 0;
		$this->swordfish += $data->swordfish > 0 ? 1 : 0;
		$this->jellyfish += $data->jellyfish > 0 ? 1 : 0;
	}
}

class CandidateIsolated
{
	public $naked2;
	public $naked3;
	public $naked4;
	public $hidden1;
	public $hidden2;
	public $hidden3;
	public $hidden4;
	public $omissions;
	public $uniqueRectangle;
	public $yWing;
	public $xyzWing;
	public $xWing;
	public $swordfish;
	public $jellyfish;

	function __construct()
	{
		$this->naked2 = 0;
		$this->naked3 = 0;
		$this->naked4 = 0;
		$this->hidden1 = 0;
		$this->hidden2 = 0;
		$this->hidden3 = 0;
		$this->hidden4 = 0;
		$this->omissions = 0;
		$this->uniqueRectangle = 0;
		$this->yWing = 0;
		$this->xyzWing = 0;
		$this->xWing = 0;
		$this->swordfish = 0;
		$this->jellyfish = 0;
	}
}

class CandidateIsolatedCounter extends CandidateIsolated
{
	public $max;

	function __construct()
	{
		parent::__construct();
		$this->max = new CandidateIsolated();
	}

	function addData($data)
	{
		foreach ($this as $thisKey => $thisValue) {
			if ($data[$thisKey] > 0) {
				foreach ($this as $key => $value) {
					if ($thisKey == $key) continue;
					if ($data[$key] > 0) return false;
				}
				$dataValue = $data[$thisKey];
				$this[$thisKey] += $dataValue;
				$this->max[$thisKey] = max($this->max[$thisKey], $dataValue);
				return true;
			}
		}
		return false;
	}
}

class StrategyCounter
{
	public $totalPuzzles;

	public $clueCounter;

	public $simples;
	public $simplesMinimal;
	public $simplesIsolated;
	public $candidatesVisible;
	public $candidates;
	public $candidatesStrategy;
	public $candidatesMinimal;
	public $candidatesIsolated;
	public $unsolvable;

	function __construct()
	{
		$this->totalPuzzles = 0;

		$this->simples = new SimpleCounter();
		$this->simplesMinimal = new SimpleCounter();
		$this->simplesIsolated = new SimpleIsolatedCounter();
		$this->candidatesVisible = new VisibleCounter();
		$this->candidates = new CandidateCounter();
		$this->candidatesStrategy = new CandidateCounter();
		$this->candidatesMinimal = new CandidateCounter();
		$this->candidatesIsolated = new CandidateIsolatedCounter();
		$this->unsolvable = new CandidateCounter();
	}

	function addData($data)
	{
		$this->totalPuzzles++;

		if ($data->solveType === 0 || $data->solveType === 1) $this->simples->addData($data);
		if ($data->solveType === 1) $this->simplesMinimal->addData($data);
		if ($data->solveType === 1) $this->simplesIsolated->addData($data);

		if ($data->solveType === 2 || $data->solveType === 3 || $data->solveType === 4) $this->candidates->addData($data);
		if ($data->solveType === 2)  $this->candidatesVisible->addData($data);
		if ($data->solveType === 3 || $data->solveType === 4) $this->candidatesStrategy->addData($data);
		if ($data->solveType === 4)  $this->candidatesMinimal->addData($data);
		if ($data->solveType === 4)  $this->candidatesIsolated->addData($data);

		if ($data->solveType === 5) $this->unsolvable->addData($data);
	}

	function lines()
	{
		// const res = 10000;
		// const percent = (val, total = this.totalPuzzles) => {
		// 	return ((Math.ceil(100 * res * val / total) / res).toFixed(3) + "%").padStart(7, "0");
		// }
		// const makeLineSimple = (title, val, total) => {
		// 	return title + ": " + percent(val, total);
		// };
		// const makeLine = (title, val, total) => {
		// 	return title + ": " + percent(val, total) + " - " + val.toLocaleString();
		// };
		// const printLine = (title, val, total) => {
		// 	lines.push(makeLine(title, val, total));
		// };

		// const lines = [];

		// const clues = [...this.clueCounter.entries()];
		// clues.sort((a, b) => {
		// 	return a[0] - b[0];
		// });

		// lines.push("--- Clues");
		// for (const clue of clues) printLine(clue[0], clue[1], this.totalPuzzles);

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
	}
}

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

function tableStatement($tableCount, $countName, $tableName, $logic)
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
	if (count($unions) === 1) {
		$unionString = $unions[0];
		$sql .= "$unionString LIMIT 1000000;\n";
	} else {
		$unionString = implode(") \n  UNION ALL\n  (", $unions);
		$sql .= "SELECT `id`, `$countName`, `puzzle` FROM (\n  ($unionString)\n  LIMIT 1000000\n) as puzzles;\n";
	}

	$sql .= "ALTER TABLE `$tableName` AUTO_INCREMENT=1;\n";
	return $sql;
}

if (!isset($_GET['mode'])) die;

// 0 = Populate Statements
// 1 = Populated Tables
// 2 = Totals
// 3 = Visual
// 4 = Strategies
// 5 = Clues

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

	if ($mode > 0) {
		$tableFormat = number_format($tableCount);
		$tableSyntax = $tableCount === 1 ? "table" : "tables";
		$totalFormat = number_format($totalCount);
		echo "$totalFormat puzzles in $tableFormat $tableSyntax<br/><br/>";
	}

	if ($mode === 0) {
		$logic = "`solveType`=1 AND `hiddenSimple`>0 AND `omissionSimple`=0 AND `nakedSimple`=0";
		$sql = tableStatement($tableCount, "hiddenSimple", "simple_hidden", $logic);
		echo "$sql\n";

		$logic = "`solveType`=1 AND `hiddenSimple`>0 AND `omissionSimple`>0 AND `nakedSimple`=0";
		$sql = tableStatement($tableCount, "omissionSimple", "simple_omission", $logic);
		echo "$sql\n";

		$logic = "`solveType`=1 AND `hiddenSimple`>0 AND `omissionSimple`=0 AND `nakedSimple`>0";
		$sql = tableStatement($tableCount, "nakedSimple", "simple_naked", $logic);
		echo "$sql\n";

		$logic = "`solveType`=1 AND `hiddenSimple`>0 AND `omissionSimple`>0 AND `nakedSimple`>0";
		$sql = tableStatement($tableCount, "clueCount", "simple_all", $logic);
		echo "$sql\n";

		$sql1 = "DROP TABLE IF EXISTS `simple_hidden`;
		CREATE TABLE `simple_hidden` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`puzzle_id` int(10) unsigned NOT NULL,
			`count` tinyint(3) unsigned NOT NULL,
			`table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		INSERT INTO `simple_hidden` (`puzzle_id`, `count`, `table`)
		SELECT `id`, `simple_hidden`, 'puzzles001'
		FROM `puzzles001` WHERE `solveType`=1 AND 
		`hiddenSimple`>0 AND 
		`omissionSimple`=0 AND 
		`nakedSimple`=0 AND 
		`nakedVisible`=0 AND 
		`naked2`=0 AND 
		`naked3`=0 AND 
		`naked4`=0 AND 
		`hidden1`=0 AND 
		`hidden2`=0 AND 
		`hidden3`=0 AND 
		`hidden4`=0 AND 
		`omissions`=0 AND 
		`uniqueRectangle`=0 AND 
		`yWing`=0 AND 
		`xyzWing`=0 AND 
		`xWing`=0 AND 
		`swordfish`=0 AND 
		`jellyfish`=0 
		LIMIT 1000000;
		ALTER TABLE `simple_hidden` AUTO_INCREMENT=1";
		$sql2 = "DROP TABLE IF EXISTS `unsolvable`;
		CREATE TABLE `unsolvable` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`puzzle_id` int(10) unsigned NOT NULL,
			`count` tinyint(3) unsigned NOT NULL,
			`table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		INSERT INTO `unsolvable` (`puzzle_id`, `count`, `table`)
		SELECT `id`, `clueCount`, 'puzzles001'
		FROM `puzzles001` WHERE `solveType`=5 AND 
		`hiddenSimple`=0 AND 
		`omissionSimple`=0 AND 
		`nakedSimple`=0 AND 
		`nakedVisible`=0 AND 
		`naked2`=0 AND 
		`naked3`=0 AND 
		`naked4`=0 AND 
		`hidden1`=0 AND 
		`hidden2`=0 AND 
		`hidden3`=0 AND 
		`hidden4`=0 AND 
		`omissions`=0 AND 
		`uniqueRectangle`=0 AND 
		`yWing`=0 AND 
		`xyzWing`=0 AND 
		`xWing`=0 AND 
		`swordfish`=0 AND 
		`jellyfish`=0 
		LIMIT 1000000;
		ALTER TABLE `unsolvable` AUTO_INCREMENT=1";
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
		WHERE `solveType`=3 OR `solveType`=4
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
		foreach ($strategies as $strategy) {
			$sql = "DROP TABLE IF EXISTS `$strategy`;\n";
			echo $sql;

			$sql = "CREATE TABLE `$strategy` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`puzzle_id` int(10) unsigned NOT NULL,
	`count` tinyint(3) unsigned NOT NULL,
	`table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;\n";
			echo $sql;

			$table = 'puzzles001';
			$sql = "INSERT INTO `$strategy` (`puzzle_id`, `count`, `table`)
SELECT `id`, `clueCount`, '$table'
FROM `$table` WHERE `solveType`=5 AND 
`hiddenSimple`=0 AND 
`omissionSimple`=0 AND 
`nakedSimple`=0 AND 
`nakedVisible`=0 AND 
`naked2`=0 AND 
`naked3`=0 AND 
`naked4`=0 AND 
`hidden1`=0 AND 
`hidden2`=0 AND 
`hidden3`=0 AND 
`hidden4`=0 AND 
`omissions`=0 AND 
`uniqueRectangle`=0 AND 
`yWing`=0 AND 
`xyzWing`=0 AND 
`xWing`=0 AND 
`swordfish`=0 AND 
`jellyfish`=0 
LIMIT 1000000;\n";
			echo $sql;

			$sql = "ALTER TABLE `$strategy` AUTO_INCREMENT=1;\n";
			echo $sql;
		}

		flushOut("--- Strategies Isolated");

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
		flushOut("--- Totals");

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
		$simpleMinimal = $counts[1];
		$simple += $simpleMinimal;

		$candidateVisual = $counts[2];
		$candidate = $counts[3];
		$candidateMinimal = $counts[4];
		$candidate += $candidateMinimal;
		$unsolvable = $counts[5];

		$percent = percentage($simple, $totalCount, 2);
		$percentMinimal = percentage($simpleMinimal, $simple, 2);
		echo "Simple: $percent ($percentMinimal minimal)\n";

		$percent = percentage($candidateVisual, $totalCount, 2);
		$percentVisual = percentage($candidateVisual, $candidateVisual + $candidate, 2);
		echo "Visual: $percent ($percentVisual of candidates)\n";

		$percent = percentage($candidate, $totalCount, 2);
		$percentMinimal = percentage($candidateMinimal, $candidate, 2);
		echo "Strategy: $percent ($percentMinimal minimal)\n";

		$percent = percentage($unsolvable, $totalCount, 2);
		echo "Unsolvable: $percent\n";

		echo "\n";
	}

	if ($mode === 3) {
		flushOut("--- Simples");

		$sql = "
SELECT solveType, clueCount, 
MAX(hiddenSimpleMax) as hiddenSimpleMax, 
MAX(omissionSimpleMax) as omissionSimpleMax, 
MAX(nakedSimpleMax) as nakedSimpleMax, 
SUM(`count`) as count FROM
(
SELECT 
			`hiddenSimple`>0 as hiddenSimple, MAX(`hiddenSimple`) as hiddenSimpleMax, 
			`omissionSimple`>0 as omissionSimple, MAX(`omissionSimple`) as omissionSimpleMax, 
			`nakedSimple`>0 as nakedSimple, MAX(`nakedSimple`) as nakedSimpleMax, 
			`solveType` as solveType, `clueCount`, COUNT(*) as count FROM puzzles001 as puzzles
			WHERE solveType<=1
			GROUP BY hiddenSimple, omissionSimple, nakedSimple, solveType, clueCount
UNION ALL
SELECT 
			`hiddenSimple`>0 as hiddenSimple, MAX(`hiddenSimple`) as hiddenSimpleMax, 
			`omissionSimple`>0 as omissionSimple, MAX(`omissionSimple`) as omissionSimpleMax, 
			`nakedSimple`>0 as nakedSimple, MAX(`nakedSimple`) as nakedSimpleMax, 
			`solveType` as solveType, `clueCount`, COUNT(*) as count FROM puzzles002 as puzzles
			WHERE solveType<=1
			GROUP BY hiddenSimple, omissionSimple, nakedSimple, solveType, clueCount
)
 as puzzles GROUP BY hiddenSimple, omissionSimple, nakedSimple, `solveType`, `clueCount`;

SELECT clueCount, 
MAX(hiddenSimpleMax) as hiddenSimple, 
MAX(omissionSimpleMax) as omissionSimple, 
MAX(nakedSimpleMax) as nakedSimple, 
MAX(nakedVisibleMax) as nakedVisiblemax, 
SUM(`count`) as count FROM
(
SELECT 
			`hiddenSimple`>0 as hiddenSimple, MAX(`hiddenSimple`) as hiddenSimpleMax, 
			`omissionSimple`>0 as omissionSimple, MAX(`omissionSimple`) as omissionSimpleMax, 
			`nakedSimple`>0 as nakedSimple, MAX(`nakedSimple`) as nakedSimpleMax, 
			MAX(`nakedVisible`) as nakedVisibleMax, 
			`clueCount`, COUNT(*) as count FROM puzzles001 as puzzles
			WHERE solveType=2
			GROUP BY hiddenSimple, omissionSimple, nakedSimple, clueCount
UNION ALL
SELECT 
			`hiddenSimple`>0 as hiddenSimple, MAX(`hiddenSimple`) as hiddenSimpleMax, 
			`omissionSimple`>0 as omissionSimple, MAX(`omissionSimple`) as omissionSimpleMax, 
			`nakedSimple`>0 as nakedSimple, MAX(`nakedSimple`) as nakedSimpleMax, 
			MAX(`nakedVisible`) as nakedVisibleMax, 
			`clueCount`, COUNT(*) as count FROM puzzles002 as puzzles
			WHERE solveType=2
			GROUP BY hiddenSimple, omissionSimple, nakedSimple, clueCount
)
 as puzzles GROUP BY hiddenSimple, omissionSimple, nakedSimple, clueCount;
		";
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

		$sql = "SELECT 
			puzzles.`hiddenSimple`>0 as hiddenSimple, MAX(puzzles.`hiddenSimple`) as hiddenSimpleMax, 
			puzzles.`omissionSimple`>0 as omissionSimple, MAX(puzzles.`omissionSimple`) as omissionSimpleMax, 
			puzzles.`nakedSimple`>0 as nakedSimple, MAX(puzzles.`nakedSimple`) as nakedSimpleMax, 
			puzzles.`nakedVisible`>0 as nakedVisible, MAX(puzzles.`nakedVisible`) as nakedVisibleMax, 
			puzzles.`solveType` as solveType, COUNT(*) as count FROM puzzles001 as puzzles
			WHERE solveType<=2
			GROUP BY hiddenSimple, omissionSimple, nakedSimple, nakedVisible, solveType";
	}

	if ($mode === 4) {
		flushOut("--- Strategies");

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
			WHERE solveType=3 OR solveType=4
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

	if ($mode === 5) {
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

	if ($mode > 0) {
		$time = (time() - $time) . "s";
		echo $time;	
	}
} catch (PDOException $e) {
	// echo "Error: " . $e->getMessage();
}
?>
	</pre>
</body>

</html>