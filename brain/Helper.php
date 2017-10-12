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
			'detail_amont_item',
			'detail_amont_escrow_fee',
			'detail_amont_delivery_fee',
			'detail_amont_subsidy_fee',
			'detail_amont_promo_discount',
			'detail_amont_transaction_fee_buyer',
			'detail_amont_extra',
			'detail_amont_margin',
			'detail_amont_insurance_fee',
			'detail_amont_credit_card_mdr_fee',
			'detail_amont_transaction_fee_seller',
		];
	}
}