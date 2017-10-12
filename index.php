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
	$formula = '3*K';
	$precision = 2; // Number of digits after the decimal point
	$parser = new FormulaParser($formula, $precision);
	$parser->setValidVariables(['K']);
    $parser->setVariables(['K' => 8]);
    $result = $parser->getResult(); // [0 => 'done', 1 => 16.38]

    return $response->write("Result $formula = " . json_encode($result));
});

$app->get('/editor', function ($request, $response, $args) {
	$columns = Helper::getColumnsSorted($this);

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

$app->get('/formula', function ($request, $response, $args) {
	$columns = Helper::getColumnsSorted($this);

	$merchantIds = [10, 20, 30];
	$paymentMethods = ['klikbca', 'creditcard', 'sakuku'];
	$fields = Helper::getInitVariables();

	$rearranged = Helper::getFormulasRearranged($this);

	return $this->view->render($response, 'formula.phtml', [
		'columns' => $columns,
		'rearranged' => $rearranged,
		'merchantIds' => $merchantIds,
		'paymentMethods' => $paymentMethods,
		'fields' => $fields,
	]);
});

$app->post('/formula', function ($request, $response, $args) {
	$this->database->query('DELETE FROM formulas');
	$formulas = $request->getParsedBodyParam('formula');

	foreach ($formulas as $merchantId => $pairLevel2) {
		foreach ($pairLevel2 as $paymentMethod => $pairLevel3) {
			foreach ($pairLevel3 as $columnId => $formulaText) {
				$this->database->insert('formulas', [
					'merchant_id' => $merchantId,
					'payment_method' => $paymentMethod,
					'column_id' => $columnId,
					'formula' => $formulaText,
				]);
			}
		}
	}
	return $response->withRedirect('/formula');
});

$app->get('/simulate-form', function ($request, $response, $args) {
	$fields = Helper::getInitVariables();
	return $this->view->render($response, 'simulate-form.phtml', [
		'fields' => $fields,
	]);
});

$app->post('/simulate-result', function ($request, $response, $args) {
	$variables = [];
	foreach (Helper::getInitVariables() as $field) {
		$variables[$field] = $request->getParsedBodyParam($field);
	}

	$merchantId = $variables['merchant_id'];
	$paymentMethod = $variables['payment_method'];

	$rearranged = Helper::getFormulasRearranged($this);
	$formulaUsed = $rearranged[$merchantId][$paymentMethod];

	// Calculate
	foreach ($formulaUsed as $columnId => $formulaText) {
	    $parser = new FormulaParser($formulaText);
	    $parser->setValidVariables(array_keys($variables));
	    $parser->setVariables($variables);
	    $result = $parser->getResult();
	    $resultValue = $result[1];
	    $variables[$columnId] = $resultValue;
	}

	return $this->view->render($response, 'simulate-result.phtml', [
		'variables' => $variables,
		'formulaUsed' => $formulaUsed,
	]);
});

$app->run();