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
		header("Location: ./play");
	}

	$score = $_SESSION["score"];
	$cScore = $_SESSION["cScore"];

?>

<title>Claude</title>
<style>
	html { background: whitesmoke; font-family: sans-serif; }
	body { max-width: 450px; margin: auto; background: #fff; padding: 2rem; }
	button { background: #eee; border: 1px solid #ddd; font: inherit; padding: 0.5rem 1rem; border-radius: 5px }
	button.big { padding: 1rem 0; width: 100% }
	button.big:first-child { border-radius: 5px 0 0 5px }
	button.big:last-child { border-radius: 0 5px 5px 0 }
	a { color: #3498db }
	hr { border: 0; border-bottom: 1px solid #ddd; margin: 2rem 0 }
</style>

<form method="post">
	<h3>Claude</h3>
	<p>Select an option. Randomly.</p>
	<div style="display: flex; justify-content: space-between">
		<button class="big" name="choice" value="L" type="submit">&larr; Left</button>
		<button class="big" name="choice" value="R" type="submit">Right &rarr;</button>
	</div>
	<div class="scores" style="margin-top: 2rem">
		<div style="display: flex; justify-content: space-between">
			<div style="width: 30%">Player (<?= $score ?>)</div>
			<div style="width: 100%"><progress value="<?= $score ?>" max="<?= $score + $cScore ?>" style="width: 100%"><?= $score ?></progress></div>
		</div>
		<div style="display: flex; justify-content: space-between; margin-top: 0.5rem">
			<div style="width: 30%">Claude (<?= $cScore ?>)</div>
			<div style="width: 100%"><progress value="<?= $cScore ?>" max="<?= $cScore + $score ?>" style="width: 100%"><?= $cScore ?></progress></div>
		</div>
	</div>
	<?php if ($lastGuess) { ?><p><strong> <?= $lastGuess === "Correct" ? "Claude got it right!" : "You won!" ?> </strong></p><?php } ?>
	<hr>
	<h4>Info</h4>
	<p>Your code is <strong><?= strtoupper(implode("-", str_split(substr(session_id(), 0, 6), 2))); ?></strong>. You can share it with your friends to keep track of what the computer is guessing.</p>
	<p>This game is using a Naive Bayes classifier, a probabilistic classifiers based on an application of Bayes' theorem.</p>
	<p>By playing this game, you agree to our use of cookies to store your playing score, choices, and code. <a href="https://oswaldlabs.com/cookies">Cookie Policy</a></p>
	<p><button name="restart">Restart</button></p>
	<p><small style="color: #ccc">My next guess: <?= $iThink === "R" ? "Right" : "Left" ?></small></p>
</form>