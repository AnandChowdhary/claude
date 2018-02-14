<?php

	require_once __DIR__ . "/vendor/autoload.php";

	session_start();

	if (!$_SESSION["score"]) {
		$_SESSION["score"] = 0;
	}

	if (!$_SESSION["cScore"]) {
		$_SESSION["cScore"] = 0;
	}

	if (!$_SESSION["samples"]) {
		$_SESSION["samples"] = [[]];
	}

	if (!$_SESSION["labels"]) {
		$_SESSION["labels"] = [["L", "R"][array_rand(["L", "R"])]];
	}

	use Phpml\Classification\NaiveBayes;
	$classifier = new NaiveBayes();
	$classifier->train($_SESSION["samples"], $_SESSION["labels"]);
	$iThink = $classifier->predict([ end($_SESSION["samples"]) ])[0];

	if (isset($_POST["choice"])) {
		array_push($_SESSION["labels"], $_POST["choice"]);
		$currentArr = $_SESSION["labels"];
		array_push($currentArr, $_POST["choice"]);
		array_push($_SESSION["samples"], $currentArr);
		if ($_POST["choice"] == $iThink) {
			$lastGuess = "Correct";
			$_SESSION["cScore"] = intval($_SESSION["cScore"]) + 1;
		} else {
			$lastGuess = "Incorrect";
			$_SESSION["score"] = intval($_SESSION["score"]) + 1;
		}
	} else if (isset($_POST["restart"])) {
		session_destroy();
		session_unset();
	}

	$score = $_SESSION["score"];
	$cScore = $_SESSION["cScore"];

?>

<style>
	body { font-family: sans-serif; padding: 10vw 10vh }
	button { background: #eee; border: 1px solid #ddd; font: inherit; padding: 0.5rem 1rem; margin: 1rem; border-radius: 5px }
</style>

<form method="post">
	<h3>Claude</h3>
	<p>Your code is <strong><?= strtoupper(implode("-", str_split(substr(session_id(), 0, 6), 2))); ?></strong>. You can share it with your friends to keep track of what the computer is guessing.</p>
	<p>Select an option. Randomly.</p>
		<button name="choice" value="L" type="submit">&larr; Left</button>
		<button name="choice" value="R" type="submit">Right &rarr;</button>
	<p>
		Your score: <strong><?= $score ?></strong> &middot;
		Computer score: <strong><?= $cScore ?></strong>
	</p>
	<?php if ($lastGuess) { ?><p>Last guess: <?= $lastGuess ?></p><?php } ?>
	<p>This game uses cookies to store your code.</p>
	<p><button name="restart">Restart</button></p>
	<p><small style="color: #ccc">I think: <?= $iThink === "R" ? "Right" : "Left" ?></small></p>
</form>