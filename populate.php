<!doctype html>
<html>

<head>
	<title>Populate</title>
</head>

<body>
	<pre>
<?php
$log = true;

function flushOut($message)
{
	echo "$message<br/>";
}

function truncate($db, $table, $log)
{
	$sql = "TRUNCATE TABLE `" . $table . "`;";
	flushOut($sql);
	if ($log) return;

	$stmt = $db->prepare($sql);
	$stmt->execute();
}

function process($db, $sql, $strategy, $log)
{
	flushOut($sql . ";");
	if (!$log) {
		$stmt = $db->prepare($sql);
		$stmt->execute();
	}

	$sql = "ALTER TABLE `" . $strategy . "` AUTO_INCREMENT=1";
	flushOut($sql . ";<br/>");
	if (!$log) {
		$stmt = $db->prepare($sql);
		$stmt->execute();
	}
}

function insert($db, $strategy, $table, $log)
{
	$sql = "INSERT INTO `" . $strategy . "` (`puzzle_id`, `table`)
SELECT `id`, '" . $table . "'
FROM `" . $table . "` WHERE `" . $strategy . "`>0";
	process($db, $sql, $strategy, $log);
}

try {
	$servername = "localhost";
	$username = "snovakow";
	$password = "kewbac-recge1-Fiwpux";
	$dbname = "sudoku";
	$db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

	$strategies = [
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
	];

	if ($table === 'truncate') {
		truncate($db, 'simple', $log);
		truncate($db, 'bruteForce', $log);
	} else {
		insert($db, 'simple',  $table, $log);
		insert($db, 'bruteForce',  $table, $log);
	}

	foreach ($strategies as $strategy) {
		if ($table === 'truncate') {
			truncate($db, $strategy, $log);
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
			process($db, $sql, $strategy, $log);
		}
	}
} catch (PDOException $e) {
	// echo "Connection failed: " . $e->getMessage();
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

?>
	</pre>
</body>

</html>