<?php
if (!isset($_GET['table'])) die();
$table = $_GET['table'];

$log = isset($_GET['log']);

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

function flushOut($message)
{
	echo $message . "<br/>";
}

function truncate($db, $table, $log)
{
	$sql = "TRUNCATE TABLE `" . $table . "`;";
	flushOut($sql);
	if ($log) return;

	$statement = $db->prepare($sql);
	$statement->execute();
}

function process($db, $sql, $strategy, $log)
{
	flushOut($sql . ";");
	if (!$log) {
		$statement = $db->prepare($sql);
		$statement->execute();
	}

	$sql = "ALTER TABLE `" . $strategy . "` AUTO_INCREMENT=1";
	flushOut($sql . ";<br/>");
	if (!$log) {
		$statement = $db->prepare($sql);
		$statement->execute();
	}
}

function insert($db, $strategy, $table, $log)
{
	$sql = "
		INSERT INTO `" . $strategy . "` (`puzzle_id`, `table`)
		SELECT `id`, '" . $table . "'
		FROM `" . $table . "` WHERE `" . $strategy . "`>0
	";
	process($db, $sql, $strategy, $log);
}

try {
	$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$strategies = array(
		"naked2",
		"naked3",
		"naked4",
		"hidden2",
		"hidden3",
		"hidden4",
		"omissions",
		"yWing",
		"xyzWing",
		"xWing",
		"swordfish",
		"jellyfish",
		"uniqueRectangle"
	);

	if ($table === 'truncate') {
		truncate($pdo, 'simple', $log);
		truncate($pdo, 'bruteForce', $log);
	} else {
		insert($pdo, 'simple',  $table, $log);
		insert($pdo, 'bruteForce',  $table, $log);
	}

	foreach ($strategies as $strategy) {
		if ($table === 'truncate') {
			truncate($pdo, $strategy, $log);
		} else {
			$sql = "
				INSERT INTO `" . $strategy . "` (`puzzle_id`, `count`, `table`)
				SELECT `id`, `has_" . $strategy . "`, '" . $table . "'
				FROM `" . $table . "` WHERE  `solveType`=1  AND `has_" . $strategy . "`>0
			";
			foreach ($strategies as $name) {
				if ($name == $strategy) continue;
				$sql .= " AND `has_" . $name . "`=0";
			}
			process($pdo, $sql, $strategy, $log);
		}
	}
} catch (PDOException $e) {
	echo "Connection failed: " . $e->getMessage();
}

echo "Complete!<br/>";

if ($log && $table !== 'truncate') {
	flushOut("<br/>SELECT COUNT(`id`) AS 'Count', MAX(`id`) AS 'Max' FROM `" . $table . "`;");
	$sql =
		"SELECT t1.id+1 AS Missing FROM `" . $table . "` t1 LEFT JOIN `" . $table . "` t2 ON t2.id = t1.id+1
		WHERE t2.id IS NULL ORDER BY t1.id";
	flushOut($sql . ";");
	flushOut("ALTER TABLE `" . $table . "` AUTO_INCREMENT=1;");
}

$pdo = null;

/*
INSERT INTO `phistomefel` (`puzzle_id`, `count`)
SELECT `id`
FROM `puzzlesPhistomefel` AS p
WHERE p.`naked2`=0 AND p.`naked3`=0 AND p.`naked4`=0 AND p.`hidden2`=0 AND p.`hidden3`=0 AND p.`hidden4`=0 AND p.`omissions`=0 AND p.`yWing`=0 AND p.`xyzWing`=0 AND p.`xWing`=0 AND p.`swordfish`=0 AND p.`jellyfish`=0 AND p.`uniqueRectangle`=0 AND p.`bruteForce`=0
*/
