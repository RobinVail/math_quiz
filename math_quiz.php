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
