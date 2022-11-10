<?php

namespace modules\wallet\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%wallet_gateway_customer_rank}}".
 *
 * @property int $wallet_gateway_id
 * @property int $customer_rank_id
 */
class GatewayCustomerRank extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%wallet_gateway_customer_rank}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['customer_rank_id', 'wallet_gateway_id'], 'integer'],
			[['customer_rank_id', 'wallet_gateway_id'], 'required'],
			[['customer_rank_id', 'wallet_gateway_id'], 'unique', 'targetAttribute' => ['customer_rank_id', 'wallet_gateway_id']],
		];
	}
}
