<?php

require 'vendor/autoload.php';

use FormulaParser\FormulaParser;
use Medoo\Medoo;



$app = new Slim\App();

$container = $app->getContainer();

$container['database'] = function ($container) {
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

$app->get('/editor', function ($request, $response, $args) {
	$columns = $this->database->select('columns', '*', [
		'ORDER' => ['position' => 'ASC'],
	]);
	return $this->view->render($response, 'editor.phtml', [
		'columns' => $columns,
	]);
});

$app->post('/editor', function ($request, $response, $args) {
	$this->database->query('DELETE FROM columns');
	$ids = $request->getParsedBodyParam('column_id');
	$labels = $request->getParsedBodyParam('column_label');

	foreach ($ids as $position => $id) {
		$label = $labels[$position];
		$this->database->insert('columns', [
			'id' => $id,
			'label' => $label,
			'position' => $position,
		]);
	}
	return $response->withRedirect('/editor');
});

$app->get('/hello', function ($request, $response, $args) {
	return $this->view->render($response, 'hello.php');
});

$app->run();