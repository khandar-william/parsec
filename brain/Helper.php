<?php

class Helper {
	public static function getColumnsSorted($app) {
		return $app->database->select('columns', '*', [
			'ORDER' => ['position' => 'ASC'],
		]);
	}

	public static function getFormulasRearranged($app) {
		$formulas = $app->database->select('formulas', '*');
		$rearranged = [];
		foreach ($formulas as $formula) {
			$rearranged[$formula['merchant_id']][$formula['payment_method']][$formula['column_id']] = $formula['formula'];
		}
		return $rearranged;
	}

	public static function getInitVariables() {
		return [
			'merchant_id',
			'payment_method',
			'release_amount',
			'refund_amount',
			'charge_amount',
			/*
			'detail_amount_item',
			'detail_amount_escrow_fee',
			'detail_amount_delivery_fee',
			'detail_amount_subsidy_fee',
			'detail_amount_promo_discount',
			'detail_amount_transaction_fee_buyer',
			'detail_amount_extra',
			'detail_amount_margin',
			'detail_amount_insurance_fee',
			'detail_amount_credit_card_mdr_fee',
			'detail_amount_transaction_fee_seller',
			*/
		];
	}
}