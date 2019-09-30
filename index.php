<?php

session_start();

$debug = !empty($_GET['debug']);

if ($debug) {
    $start_time = microtime(true);
}
?>
<html>
<head>

    <title>😀 NUMBER 😀</title>

    <meta charset="UTF-8">

    <style>
        * {
            font-family: arial;
        }

        #thy_number {
            font-weight: bold;
            font-size: 20px;
        }

        #thou_art_number_one {
            font-size: 60px;
            font-weight: bold;
        }
    </style>

</head>
<body>

<h2> #⃣ 😀 *~~'== NUMBER =='~~*  &#x1F608; &#x1F60D;</h2>

<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$num_numbers_to_keep = 50;
$nap_seconds = 0.1; // Naps currently run twice

if (strlen($_POST['name'] ?? '') > 12) {
    $_POST['name'] = substr($_POST['name'], 0, 12);
}

if (!empty(trim($_POST['name'] ?? ''))) {
    $name = $_POST['name'];
    setcookie('name', $name);
} elseif (!empty($_COOKIE['name'])) {
    $name = $_COOKIE['name'];
} else {
    $name = '';
}

$while_count = 0;
$numbers = [];

while (
    empty($num_numbers)
    || $num_numbers < $num_numbers_to_keep
) {

    usleep($nap_seconds * 1000000);
    $while_count++;

    if ($while_count >= 10) {
        die('something went wrong :(');
    }

    $filesize = filesize('num-numbers.txt');

    if (empty($filesize)) {
        continue;
    }

    $num_numbers_file = fopen('num-numbers.txt', 'r');

    if (!flock($num_numbers_file, LOCK_EX)) {
        continue;
    }

    $num_numbers = fread($num_numbers_file, $filesize) + 1;
}

$num_numbers_file = fopen('num-numbers.txt', 'w');
fwrite($num_numbers_file, $num_numbers);
flock($num_numbers_file, LOCK_UN);
fclose($num_numbers_file);

if ($debug) {
    echo "$while_count num-numbers.txt read attempts<br><br>";
}

$max_number = $num_numbers;

$number = min([
    rand(1, $max_number),
    rand(1, $max_number),
]);

$while_count = 0;

while (
    empty($numbers)
    || count($numbers) === 1
) {

    usleep($nap_seconds * 1000000);
    $while_count++;

    if ($while_count >= 10) {
        die('something went wrong :(');
    }

    $filesize = filesize('numbers.txt');

    if (empty($filesize)) {
        continue;
    }

    $numbers_file = fopen('numbers.txt', 'r');

    if (!flock($numbers_file, LOCK_EX)) {
        continue;
    }

    $numbers = explode("\n", fread($numbers_file, $filesize));
}

if ($debug) {
    echo "$while_count numbers.txt read attempts<br><br>";
}

$numbers[] = "$number:$name";
rsort($numbers, SORT_NUMERIC);
$numbers = array_slice($numbers, 0, $num_numbers_to_keep);

$numbers_string = implode("\n", $numbers);
$numbers_file = fopen('numbers.txt', 'w');

fwrite($numbers_file, $numbers_string);
flock($numbers_file, LOCK_UN);
fclose($numbers_file);

$biggest_number = explode(":", $numbers[0])[0];
$chances_of_number_one = round(pow(($max_number - $biggest_number) / $max_number, 2) * 100, 3);
?>

<form action="" method="post">
    <p>Welcome, brave adventurer! By what name wouldst thou like to be known?<br><input name="name" value="<?= htmlspecialchars($name) ?>" maxlength="12"> <input type="submit" name="submit" value="Submit Thy Name and Receive Thy Number"></p>
</form>

<p>Numbers received may range in value from 1 to <?= number_format($max_number) ?></p>

<p>Smaller numbers are much more likely than larger numbers</p>
<p>Total numbers granted thus far: <?= number_format($num_numbers) ?></p>
<p>Thy chances of becoming #1: <?= $chances_of_number_one ?>%</p>

<p id="thy_number">Thy Number: <?= number_format($number) ?></p>

<?php

if ($biggest_number == $number) {
    ?>
    <p id="thou_art_number_one">!!!THOU ART #1!!!</p>
    <?php
}

if ($debug) {
    $end_time = microtime(true);
    $duration = $end_time - $start_time;
    ?><p><?= $duration ?> seconds (to this point)</p><?php
}
?>

<h3>Scoreboard</h3><?php

$thy_best_number = null;
$i = 0;

foreach ($numbers as $number_string) {

    $i++;
    $number_array = explode(':', $number_string);
    $number = (float)reset($number_array);
    $number_name = end($number_array) ?: 'anonymous';

    ?><p><?= "$i) ".htmlspecialchars($number_name)." - ".number_format($number) ?></p><?php

    if ($debug && $i >= 10) {
        break;
    }
}

if ($debug) {
    $end_time = microtime(true);
    $duration = $end_time - $start_time;
    ?><p><?= $duration ?> seconds (to this point)</p><?php
}

?>
</body>
</html>
