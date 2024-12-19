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

