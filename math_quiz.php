<?php
session_start();

// Function to generate a math question
function generateQuestion($level, $operator) {
    // Determine the range of numbers based on the selected level
    if ($level == 1) {
        $min = 1;
        $max = 10;
    } elseif ($level == 2) {
        $min = 11;
        $max = 100;
    } else {
        // Use custom range specified by the user
        $min = $_POST['custom_min'] ?? 1;
        $max = $_POST['custom_max'] ?? 10;
    }

    // Generate two random numbers within the range
    $num1 = rand($min, $max);
    $num2 = rand($min, $max);

    // Calculate the answer based on the selected operator
    switch ($operator) {
        case 'Addition':
            $answer = $num1 + $num2;
            $symbol = '+';
            break;
        case 'Subtraction':
            $answer = $num1 - $num2;
            $symbol = '-';
            break;
        case 'Multiplication':
            $answer = $num1 * $num2;
            $symbol = '*';
            break;
        default:
            $answer = 0;
            $symbol = '?';
            break;
    }

    // Generate multiple-choice options
    $choices = [$answer];
    while (count($choices) < 4) {
        $randomChoice = rand($min * $min, $max * $max);
        if (!in_array($randomChoice, $choices)) {
            $choices[] = $randomChoice;
        }
    }
    shuffle($choices);

    // Return the question components
    return [$num1, $num2, $symbol, $answer, $choices];
}

// Handle starting the quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_quiz'])) {
    // Reset the scores
    $_SESSION['score_correct'] = 0;
    $_SESSION['score_wrong'] = 0;

    // Save quiz settings in the session
    $_SESSION['level'] = $_POST['level'];
    $_SESSION['operator'] = $_POST['operator'];
    $_SESSION['num_items'] = $_POST['num_items'];
    $_SESSION['custom_min'] = $_POST['custom_min'] ?? null;
    $_SESSION['custom_max'] = $_POST['custom_max'] ?? null;

    // Generate the first question
    list($num1, $num2, $symbol, $answer, $choices) = generateQuestion($_SESSION['level'], $_SESSION['operator']);
    $_SESSION['current_question'] = [$num1, $num2, $symbol, $answer, $choices];
}

// Handle submitting an answer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_answer'])) {
    // Check if the user's answer is correct
    $correctAnswer = $_POST['correct_answer'];
    $userAnswer = $_POST['answer'];

    if ($userAnswer == $correctAnswer) {
        $_SESSION['score_correct']++;
    } else {
        $_SESSION['score_wrong']++;
    }

    // Generate the next question
    list($num1, $num2, $symbol, $answer, $choices) = generateQuestion($_SESSION['level'], $_SESSION['operator']);
    $_SESSION['current_question'] = [$num1, $num2, $symbol, $answer, $choices];
}

// Handle ending the game
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['end_game'])) {
    // Destroy the session and reset the quiz
    session_destroy();
    header("Location: math_quiz.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Math Quiz</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 20px; }
        .container { width: 50%; margin: auto; }
        .settings, .quiz, .score { margin: 20px; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        .quiz { background-color: #f9f9f9; }
        button { padding: 10px 20px; margin: 10px; }
    </style>
</head>
<body>
<div class="container">
    <!-- Settings Form -->
    <?php if (!isset($_SESSION['current_question'])): ?>
        <form method="POST" action="">
            <div class="settings">
                <h2>Quiz Settings</h2>
                <label>Level:
                    <select name="level" required onchange="toggleCustomRange(this.value)">
                        <option value="1">Level 1 (1-10)</option>
                        <option value="2">Level 2 (11-100)</option>
                        <option value="custom">Custom</option>
                    </select>
                </label>
                <br>
                <div id="custom-range" style="display: none;">
                    <label>Custom Min:
                        <input type="number" name="custom_min" min="1">
                    </label>
                    <br>
                    <label>Custom Max:
                        <input type="number" name="custom_max" min="1">
                    </label>
                    <br>
                </div>
                <label>Operator:
                    <select name="operator" required>
                        <option value="Addition">Addition</option>
                        <option value="Subtraction">Subtraction</option>
                        <option value="Multiplication">Multiplication</option>
                    </select>
                </label>
                <br>
                <label>Number of Questions:
                    <input type="number" name="num_items" value="10" min="1" required>
                </label>
                <br>
                <button type="submit" name="start_quiz">Start Quiz</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- Display the Quiz -->
    <?php if (isset($_SESSION['current_question'])): ?>
        <div class="quiz">
            <h2>Question</h2>
            <p>
                <?php
                $q = $_SESSION['current_question'];
                echo "{$q[0]} {$q[2]} {$q[1]} = ?";
                ?>
            </p>
            <form method="POST" action="">
                <input type="hidden" name="correct_answer" value="<?php echo $q[3]; ?>">
                <?php foreach ($q[4] as $choice): ?>
                    <label>
                        <input type="radio" name="answer" value="<?php echo $choice; ?>" required>
                        <?php echo $choice; ?>
                    </label>
                    <br>
                <?php endforeach; ?>
                <button type="submit" name="submit_answer">Submit Answer</button>
            </form>
            <form method="POST" action="">
                <button type="submit" name="end_game" style="background-color: red; color: white;">End Game</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Display the Score -->
    <div class="score">
        <h2>Score</h2>
        <p>Correct: <?php echo $_SESSION['score_correct'] ?? 0; ?></p>
        <p>Wrong: <?php echo $_SESSION['score_wrong'] ?? 0; ?></p>
    </div>
</div>

<script>
    function toggleCustomRange(value) {
        const customRange = document.getElementById('custom-range');
        customRange.style.display = (value === 'custom') ? 'block' : 'none';
    }
</script>
</body>
</html>

