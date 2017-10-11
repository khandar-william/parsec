<?php

require 'vendor/autoload.php';

use FormulaParser\FormulaParser;
use Medoo\Medoo;

$database = new Medoo([
	'database_type' => 'sqlite',
	'database_file' => 'awkward.sqlite'
]);

$database->query("CREATE TABLE IF NOT EXISTS columns (
	id TEXT,
	label TEXT,
	position INT
);");

$database->query("CREATE TABLE IF NOT EXISTS formulas (
	merchant_id TEXT,
	payment_method TEXT,
	column_id TEXT,
	formula TEXT
);");

$app = new Slim\App();

$container = $app->getContainer();

$container['database'] = function ($container) use ($database) {
	return $database;
};
$container['view'] = function ($container) {
    return new \Slim\Views\PhpRenderer('templates/');
};

$app->get('/situ', function ($request, $response, $args) {
	$formula = '3*x^2 - 4*y + 3/y';
	$precision = 2; // Number of digits after the decimal point
	$parser = new FormulaParser($formula, $precision);
    $parser->setVariables(['x' => -4, 'y' => 8]);
    $result = $parser->getResult(); // [0 => 'done', 1 => 16.38]

    return $response->write("Reult  " . json_encode($result));
});

$app->get('/asdf', function ($request, $response, $args) {
	$columns = $this->database->select('columns', '*');
	return $response->write(print_r($columns, true));
});

$app->get('/hello', function ($request, $response, $args) {
	return $this->view->render($response, 'hello.php');
});

$app->run();