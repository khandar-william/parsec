<?php

require 'vendor/autoload.php';

use FormulaParser\FormulaParser;

$app = new Slim\App();

$app->get('/situ', function ($request, $response, $args) {
	$formula = '3*x^2 - 4*y + 3/y';
	$precision = 2; // Number of digits after the decimal point
	$parser = new FormulaParser($formula, $precision);
    $parser->setVariables(['x' => -4, 'y' => 8]);
    $result = $parser->getResult(); // [0 => 'done', 1 => 16.38]

    return $response->write("Reult  " . json_encode($result));
});

$app->run();