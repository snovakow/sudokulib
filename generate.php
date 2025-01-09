<?php
if (!isset($_GET['version'])) die();
$version = (int)$_GET['version'];
if ($version !== 2) die();

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

function addTable($number)
{
	$table = tableName($number);
	return "CREATE TABLE IF NOT EXISTS `$table` (
  `id` int(10) unsigned NOT NULL,
  `puzzleData` binary(32) NOT NULL DEFAULT '00000000000000000000000000000000',
  `clueCount` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `solveType` tinyint(1) unsigned NOT NULL DEFAULT '0',
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
  `superRank` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `superSize` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `superType` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `superDepth` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `superCount` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin";
}

function insertValues($number, $values)
{
	$valueList = implode(",", $values);
	$table = tableName($number);
	return "INSERT INTO `$table` (id, puzzleData, clueCount, solveType,
		hiddenSimple, omissionSimple, naked2Simple, naked3Simple, nakedSimple,
		omissionVisible, naked2Visible, nakedVisible,
		naked2, naked3, naked4, hidden1, hidden2, hidden3, hidden4, omissions,
		uniqueRectangle, yWing, xyzWing, xWing, swordfish, jellyfish,
		superRank, superSize, superType, superDepth, superCount) VALUES $valueList";
}

try {
	$servername = "localhost";
	$username = "snovakow";
	$password = "kewbac-recge1-Fiwpux";
	$dbname = "sudoku";
	$db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

	$array = json_decode(file_get_contents("php://input"));
	$addCount = count($array);

	$db->exec("START TRANSACTION");
	$stmt = $db->prepare("SELECT `tableCount`, `puzzleCount` FROM `tables` FOR UPDATE");
	$stmt->execute();
	$result = $stmt->fetch();
	$tableCount = (int)$result['tableCount'];
	$puzzleCount = (int)$result['puzzleCount'];
	$totalRequired = totalCount($tableCount, $puzzleCount) + $addCount;

	$tableAvailable = $tableCount;
	$totalAvailable = $tableAvailable * MAX_SIZE;

	while ($totalRequired > $totalAvailable) {
		$db->exec("COMMIT");

		$tableAvailable++;
		$totalAvailable = $tableAvailable * MAX_SIZE;
		$db->exec(addTable($tableAvailable));

		$db->exec("START TRANSACTION");
		$stmt->execute();
		$result = $stmt->fetch();
		$tableCount = (int)$result['tableCount'];
		$puzzleCount = (int)$result['puzzleCount'];
		$totalRequired = totalCount($tableCount, $puzzleCount) + $addCount;

		if ($tableCount > $tableAvailable) {
			$tableAvailable = $tableCount;
			$totalAvailable = $tableAvailable * MAX_SIZE;
		}
	}

	$values = [];
	foreach ($array as $post) {
		if ($tableCount === 0 || $puzzleCount >= MAX_SIZE) {
			if (count($values) > 0) {
				$db->exec(insertValues($tableCount, $values));
				$values = [];
			}

			$tableCount++;
			$puzzleCount = 0;
		}
		$puzzleCount++;

		$valueList = [
			$puzzleCount,
			"X'$post->puzzleData'",
			$post->clueCount,
			$post->solveType,
			$post->hiddenSimple,
			$post->omissionSimple,
			$post->naked2Simple,
			$post->naked3Simple,
			$post->nakedSimple,
			$post->omissionVisible,
			$post->naked2Visible,
			$post->nakedVisible,
			$post->naked2,
			$post->naked3,
			$post->naked4,
			$post->hidden1,
			$post->hidden2,
			$post->hidden3,
			$post->hidden4,
			$post->omissions,
			$post->uniqueRectangle,
			$post->yWing,
			$post->xyzWing,
			$post->xWing,
			$post->swordfish,
			$post->jellyfish,
			$post->superRank,
			$post->superSize,
			$post->superType,
			$post->superDepth,
			$post->superCount,
		];
		$flatList = implode(',', $valueList);
		$values[] = "($flatList)";
	}

	if (count($values) > 0) $db->exec(insertValues($tableCount, $values));

	$stmt = $db->prepare("UPDATE `tables` SET `tableCount`=?, `puzzleCount`=?");
	$stmt->execute([$tableCount, $puzzleCount]);

	$db->exec("COMMIT");

	echo "$tableCount:$puzzleCount";
} catch (PDOException $e) {
	// echo "Connection failed: " . $e->getMessage();
}
