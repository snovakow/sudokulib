<?php
if (!isset($_GET['table'])) die();
$table = $_GET['table'];

$servername = "localhost";
$username = "snovakow";
$password = "kewbac-recge1-Fiwpux";
$dbname = "sudoku";

function flushOut($message)
{
	echo $message . "<br/>";
}

function truncate($db, $table)
{
	$sql = "TRUNCATE TABLE `" . $table . "`";
	flushOut($sql);
	$statement = $db->prepare($sql);
	$statement->execute();

	$sql = "TRUNCATE TABLE ";
}

function process($db, $sql, $strategy)
{
	flushOut($sql . "<br/>");
	$statement = $db->prepare($sql);
	$statement->execute();

	$sql = "SELECT MAX(`id`) AS max_id FROM `" . $strategy . "`";
	$statement = $db->prepare($sql);
	$statement->execute();
	$result = $statement->fetch();
	$id = $result['max_id'] + 1;

	$sql = "ALTER TABLE `" . $strategy . "` AUTO_INCREMENT=" . $id;
	$statement = $db->prepare($sql);
	$statement->execute();
}

function insert($db, $strategy, $table)
{
	$sql = "
		INSERT INTO `" . $strategy . "` (`puzzle_id`, `table`)
		SELECT `id`, '" . $table . "'
		FROM `" . $table . "` WHERE `" . $strategy . "`>0
	";
	process($db, $sql, $strategy);
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
		truncate($pdo, 'simple');
		truncate($pdo, 'bruteForce');
	} else {
		insert($pdo, 'simple',  $table);
		insert($pdo, 'bruteForce',  $table);
	}

	foreach ($strategies as $strategy) {
		if ($table === 'truncate') {
			truncate($pdo, $strategy);
		} else {
			$sql = "
				INSERT INTO `" . $strategy . "` (`puzzle_id`, `count`, `table`)
				SELECT `id`, `has_" . $strategy . "`, '" . $table . "'
				FROM `" . $table . "` WHERE  `bruteForce`=0  AND `has_" . $strategy . "` >0
			";
			foreach ($strategies as $name) {
				// if ($strategy == "jellyfish" && $name == "naked2") continue;
				$sql .= " AND `has_" . $name . "`" . ($name == $strategy ? ">0" : "=0");
			}
			process($pdo, $sql, $strategy);
		}
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
