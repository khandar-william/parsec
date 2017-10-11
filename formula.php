<?php
require_once __DIR__ . '/vendor/autoload.php';

use FormulaParser\FormulaParser;

$formula = '3*x^2 - 4*y + 3/y';
$precision = 2; // Number of digits after the decimal point

try {
    $parser = new FormulaParser($formula, $precision);
    $parser->setVariables(['x' => -4, 'y' => 8]);
    $result = $parser->getResult(); // [0 => 'done', 1 => 16.38]
	print_r($result);
} catch (\Exception $e) {
    echo $e->getMessage(), "\n";
}
