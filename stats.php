<!doctype html>
<html>

<head>
	<title>Stats</title>
</head>

<body>
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

	class SimpleMinimalCounter
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
			if ($data->hiddenSimple > 0 && $data->omissionSimple === 0 && $data->nakedSimple === 0) $this->hiddenSimple++;
			if ($data->hiddenSimple > 0 && $data->omissionSimple > 0 && $data->nakedSimple === 0) $this->omissionSimple++;
			if ($data->hiddenSimple > 0 && $data->omissionSimple === 0 && $data->nakedSimple > 0) $this->nakedSimple++;
			if ($data->hiddenSimple > 0 && $data->omissionSimple > 0 && $data->nakedSimple > 0) $this->allSimple++;
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
			$this->nakedVisible += $data->nakedVisible > 0 ? 1 : 0;
			$this->omissionVisible += $data->omissionVisible > 0 ? 1 : 0;
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
	/*
class StrategyCounter {
	constructor() {
		this.totalPuzzles = 0;
		this.clueCounter = new Map();

		this.simples = new SimpleCounter();
		this.simplesMinimal = new SimpleCounter();
		this.simplesIsolated = new SimpleMinimalCounter();
		this.candidatesVisible = new VisibleCounter();
		this.candidates = new CandidateCounter();
		this.candidatesMinimal = new CandidateCounter();
		this.unsolvable = new CandidateCounter();

		this.startTime = performance.now();
		this.totalTime = 0;
	}
	addData(data) {
		this.totalPuzzles++;

		if (data.solveType === 0 || data.solveType === 1) this.simples.addData(data);
		if (data.solveType === 1) this.simplesMinimal.addData(data);
		if (data.solveType === 1) this.simplesIsolated.addData(data);

		if (data.solveType === 2) this.candidatesVisible.addData(data);
		if (data.solveType === 3 || data.solveType === 4) this.candidates.addData(data);
		if (data.solveType === 4) this.candidatesMinimal.addData(data);

		if (data.solveType === 5) this.unsolvable.addData(data);

		const clueValue = this.clueCounter.get(data.clueCount);
		if (clueValue) this.clueCounter.set(data.clueCount, clueValue + 1);
		else this.clueCounter.set(data.clueCount, 1)

		this.totalTime = performance.now() - this.startTime;
	}
	lines() {
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

		// const candidateCount = this.candidates.count + this.candidatesVisible.count;

		// lines.push("");
		// lines.push("--- Totals " + this.totalPuzzles.toLocaleString());

		// let line = "Simples: " + percent(this.simples.count);
		// if (this.simples.count > 0) line += " (" + percent(this.simplesMinimal.count, this.simples.count) + " minimal)";
		// lines.push(line);

		// line = "Strategies: " + percent(this.candidates.count);
		// if (this.candidates.count > 0) line += " (" + percent(this.candidatesMinimal.count, this.candidates.count) + " minimal)";
		// lines.push(line);

		// line = "Candidates: " + percent(candidateCount);
		// if (candidateCount > 0) line += " (" + percent(this.candidatesVisible.count, candidateCount) + " visible)";
		// lines.push(line);

		// lines.push("Unsolvable: " + percent(this.unsolvable.count));

		// lines.push("");
		// lines.push("--- Rate");
		const timeAvg = this.totalTime / 1000 / this.totalPuzzles;
		// const timeAvgInv = 1 / timeAvg;
		// lines.push("Time Avg: " + timeAvg.toFixed(3));
		// lines.push("Per Second: " + timeAvgInv.toFixed(1));

		return timeAvg;
	}
}
*/
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

		$time = time();

		echo "<pre>";

		$stmt = $db->prepare("SELECT `tableCount`, `puzzleCount` FROM `tables`");
		$stmt->execute();
		$result = $stmt->fetch();
		$tableCount = (int)$result['tableCount'];
		$puzzleCount = (int)$result['puzzleCount'];
		$totalCount = totalCount($tableCount, $puzzleCount);

		$tableFormat = number_format($tableCount);
		$tableSyntax = $tableCount === 1 ? "table" : "tables";
		$totalFormat = number_format($totalCount);
		echo "$totalFormat puzzles in $tableFormat $tableSyntax<br/><br/>";

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
		}

		if ($mode === 2) {
			flushOut("--- Strategies");

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
				$unionString = implode(' UNION ALL ', $unions);
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

		$time = (time() - $time) . "s";
		echo $time;
		echo "</pre>";
	} catch (PDOException $e) {
		// echo "Error: " . $e->getMessage();
	}
	?>
</body>

</html>