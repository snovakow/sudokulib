<?php
// die();

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

$execute = true;

function flushOut($message)
{
	echo $message . "<br/>";
	ob_flush();
	flush();
}

$single = $_GET['strategy'];

try {
	$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	// $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$strategies = array("naked2", "naked3", "naked4", "hidden2", "hidden3", "hidden4", "omissions", "yWing", "xyzWing", "xWing", "swordfish", "jellyfish", "uniqueRectangle");

	$table = "puzzles2";

	if (!$single || $single == "simple") {
		$sql = "TRUNCATE TABLE `simple`";
		flushOut($sql);
		$statement = $pdo->prepare($sql);
		if ($execute) $statement->execute();

		$sql = "
			INSERT INTO `simple` (`puzzle_id`)
			SELECT `id`
			FROM `" . $table . "` AS p
			WHERE p.`simple` > 0
		";
		flushOut($sql . "<br/>");
		$statement = $pdo->prepare($sql);
		if ($execute) $statement->execute();
	}

	foreach ($strategies as $strategy) {
		if ($single && $single != $strategy) continue;

		$sql = "TRUNCATE TABLE `" . $strategy . "`";
		flushOut($sql);
		$statement = $pdo->prepare($sql);
		if ($execute) $statement->execute();

		$sql = "
			INSERT INTO `" . $strategy . "` (`puzzle_id`, `count`)
			SELECT `id`, `" . $strategy . "`
			FROM `" . $table . "` AS p
			WHERE  p.`bruteForce`=0
		";
		foreach ($strategies as $name) {
			// if ($strategy == "jellyfish" && $name == "naked2") continue;

			$sql .= " AND p.`" . $name . "`";
			if ($name == $strategy) $sql .= ">0";
			else $sql .= "=0";
		}
		flushOut($sql . "<br/>");
		$statement = $pdo->prepare($sql);
		if ($execute) $statement->execute();
	}
} catch (PDOException $e) {
	echo "Connection failed: " . $e->getMessage();
}

echo "Complete!<br/>";

$pdo = null;

/*
INSERT INTO `phistomefelRing` (`puzzle_id`, `count`)
SELECT `id`, `phistomefel`
FROM `phistomefel` AS p
WHERE p.`naked2`=0 AND p.`naked3`=0 AND p.`naked4`=0 AND p.`hidden2`=0 AND p.`hidden3`=0 AND p.`hidden4`=0 AND p.`omissions`=0 AND p.`yWing`=0 AND p.`xyzWing`=0 AND p.`xWing`=0 AND p.`swordfish`=0 AND p.`jellyfish`=0 AND p.`uniqueRectangle`=0 AND p.`phistomefel`>0 AND p.`bruteForce`=0
*/
