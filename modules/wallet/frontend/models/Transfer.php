<?php

namespace modules\wallet\frontend\models;

use Exception;
use modules\customer\frontend\models\CustomerIdentity;
use modules\game\models\ProductWallet;
use modules\game\models\Turnover;
use modules\matrix\base\Wallet as WalletAPI;
use modules\promotion\models\Promotion;
use modules\promotion\models\PromotionJoining;
use modules\wallet\models\Transaction;
use modules\wallet\models\TransferHistory;
use modules\wallet\models\Wallet;
use modules\wallet\models\WalletSub;
use modules\wallet\models\WalletSubTransaction;
use Throwable;
use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Transfer model
 *
 * @property-read Wallet $wallet
 * @property-read \modules\wallet\models\WalletSub[] $subWallets
 * @property-read array $products
 * @property-read string $currency
 * @property-read array $wallets
 * @property-read array $turnovers
 * @property-read array $promotions
 * @property-read array $playerPromotions
 * @property-read float $totalTurnovers
 */
class Transfer extends Model{

	const MAIN_WALLET = 'main';

	public $from_wallet;
	public $to_wallet;
	public $amount;
	public $promotion_id;

	/**
	 * @var Promotion
	 */
	private $_promotion = NULL;

	private $_promotion_rate = 1;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			[['from_wallet', 'to_wallet', 'amount'], 'required'],
			[['from_wallet', 'to_wallet'], 'string'],
			['amount', 'integer'],
			['amount', 'compare', 'compareValue' => 0, 'operator' => '>', 'message' => Yii::t('wallet',
				'Amount must be greater than 0.')],
			['amount', 'filter', 'filter' => 'floor'],
			['from_wallet', 'compare', 'compareAttribute' => 'to_wallet', 'operator' => '!=', 'message' => Yii::t('wallet',
				'From Wallet must not be equal to "To Wallet".')],
			['promotion_id', 'integer'],
			['promotion_id', 'exist', 'targetClass' => Promotion::class, 'targetAttribute' => ['promotion_id' => 'id']],

			['amount', 'validateFromWallet'],
			[['from_wallet', 'to_wallet'], 'validateWallet'],
			['promotion_id', 'validatePromotion'],
		];
	}

	/**
	 * @param $attribute
	 *
	 * @return void
	 * @throws \yii\base\InvalidConfigException
	 */
	public function validateFromWallet($attribute){
		if (!empty($this->$attribute)){
			$wallet = $this->findFromWallet();

			if (empty($wallet) || empty($wallet->balance)){
				$this->addError('from_wallet',
					Yii::t('wallet', 'Channel does not have enough to transfer.'));
			}elseif ($wallet->balance < $this->amount){
				$this->addError('amount',
					Yii::t('wallet', 'Amount must be less than or equal to {0}.',
						Yii::$app->formatter->asCurrency($wallet->balance)));
			}
		}
	}

	/**
	 * @param $attribute
	 *
	 * @return void
	 */
	public function validateWallet($attribute){
		if (!empty($this->$attribute) && $this->$attribute != self::MAIN_WALLET){
			$is_locked = WalletSub::find()
			                      ->andWhere(['wallet_id' => $this->wallet->id])
			                      ->andWhere(['product_code' => $this->$attribute, 'status' => WalletSub::STATUS_LOCKED])
			                      ->exists();

			if ($is_locked){
				$this->addError('from_wallet',
					Yii::t('wallet', 'Channel has been blocked due to you joined the promotion.'));
			}
		}
	}

	/**
	 * @param $attribute
	 *
	 * @return void
	 * @throws \yii\base\InvalidConfigException|\yii\base\Exception
	 */
	public function validatePromotion($attribute){
		if (!empty($this->$attribute) && ($promo_id = $this->$attribute)){
			$this->_promotion = Promotion::findOne($promo_id);
			$wallets          = ArrayHelper::getColumn($this->_promotion->productMap ?? [],
				'product_code');

			if (empty($this->_promotion) || !ArrayHelper::isIn($this->to_wallet, $wallets)){
				$this->addError($attribute, Yii::t('wallet', 'Invalid Promo Code'));

				return;
			}

			$to_wallet = $this->findToWallet();
			if ($this->_promotion->type == Promotion::TYPE_FIRST_DEPOSIT && !empty($to_wallet->balance) && $to_wallet->balance >= 1){
				$this->addError($attribute,
					Yii::t('wallet',
						'You must withdraw all balance from this channel to join the promotion'));

				return;
			}

			if (!empty($this->_promotion->min_deposit) && $this->amount < $this->_promotion->min_deposit){
				$this->addError($attribute,
					Yii::t('wallet', 'Amount must be no less than {0} to join the promotion',
						Yii::$app->formatter->asCurrency($this->_promotion->min_deposit)));

				return;
			}

			if (!empty($this->_promotion->bonus_rate)){
				$this->_promotion_rate += $this->_promotion->bonus_rate / 100;
			}
		}
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'promotion_id' => Yii::t('wallet', 'Promo Code'),
			'amount'       => Yii::t('wallet', 'Amount'),
		];
	}

	/**
	 * @return \modules\wallet\models\Wallet|null
	 */
	public function getWallet(){
		return Wallet::my();
	}

	/**
	 * @return array|\modules\wallet\models\WalletSub[]
	 */
	public function getSubWallets(){
		return $this->wallet->subWallets ?? [];
	}

	/**
	 * @return array
	 */
	public function getProducts(){
		$products = ProductWallet::find()
		                         ->select(['product_wallet.*', 'type_name' => 'type.name'])
		                         ->default()
		                         ->joinWith('type', FALSE)
		                         ->orderBy(['type.ordering' => SORT_ASC])
		                         ->groupBy(['code'])
		                         ->asArray()
		                         ->all();

		if ($sub_wallets = $this->subWallets){
			$sub_wallets = ArrayHelper::map($sub_wallets, 'product_code', 'balance');
		}

		$data = [];
		foreach ($products as $product){
			$product_data = [
				'name'  => $product['name'],
				'total' => $sub_wallets[$product['code']] ?? 0,
				'code'  => $product['code']
			];

			if (empty($data[$product['type_id']])){
				$data[$product['type_id']] = [
					'label'    => $product['type_name'],
					'products' => [$product_data]
				];
			}else{
				$data[$product['type_id']]['products'][] = $product_data;
			}
		}

		return $data;
	}

	/**
	 * @return string|null
	 */
	public function getCurrency(){
		return CustomerIdentity::profile()->currency ?? NULL;
	}

	/**
	 * @return array
	 */
	public function getWallets(){
		return $this->wallet->allWallets ?? [];
	}

	private $_promotions = NULL;

	/**
	 * @return array
	 */
	public function getPromotions(){
		if ($this->_promotions === NULL){
			$promotions = Promotion::running();

			$this->_promotions = ArrayHelper::map($promotions, 'id', function ($data){
				return [
					'id'       => $data['id'] ?? '',
					'label'    => Yii::t('wallet', $data['name'] ?? ''),
					'products' => ArrayHelper::getColumn($data['productMap'] ?? [], 'product_code')
				];
			});
		}

		return $this->_promotions;
	}

	/**
	 * @return array
	 */
	public function getPromotionList(){
		return ArrayHelper::getColumn($this->promotions, 'label');
	}

	/**
	 * @return array
	 */
	public function getPlayerPromotions(){
		$promotions = [];
		foreach ($this->promotions as $id => $promotion){
			if (empty($promotion['products']) || ArrayHelper::isIn($id, $this->joinedPromos())){
				continue;
			}

			foreach ($promotion['products'] as $product){
				$promotions[$product][] = $id;
			}
		}

		return $promotions;
	}

	private $_joined = NULL;

	/**
	 * @return array
	 */
	private function joinedPromos(){
		if ($this->_joined === NULL){
			$this->_joined = PromotionJoining::find()
			                                 ->alias('joining')
			                                 ->joinWith('promotion promotion', FALSE)
			                                 ->andWhere(['joining.player_id' => $this->wallet->customer->username ?? NULL])
			                                 ->andWhere(['promotion.status' => Promotion::STATUS_RUNNING])
			                                 ->select(['promotion.id'])
			                                 ->column();
		}

		return $this->_joined;
	}

	/**
	 * @var \modules\wallet\models\Transaction|WalletSubTransaction
	 */
	private $_transaction = NULL;

	/**
	 * @throws \yii\base\Exception
	 */
	public function submit(){
		if ($this->validate()){
			$from_wallet = $this->findFromWallet();
			$to_wallet   = $this->findToWallet();

			$transferred = $this->transfer($from_wallet, $to_wallet);

			if (!empty($transferred) && $to_wallet instanceof WalletSub){
				if (!empty($this->_promotion)){
					if ($this->_promotion_rate > 1){
						$bonus = $this->amount * (1 - $this->_promotion_rate);
					}else{
						$bonus = 0;
					}

					$promo = [
						'promotion_id' => $this->promotion_id,
						'player_id'    => $this->wallet->customer->username ?? NULL,
						'wallet_id'    => $to_wallet->id,
						'promotion'    => $this->_promotion,
						'wallet_code'  => $this->to_wallet,
						'bonus'        => abs($bonus)
					];

					PromotionJoining::store($promo);
				}
			}

			if (!empty($this->_transaction)){
				$status = $transferred ? Transaction::STATUS_SUCCESS : Transaction::STATUS_FAILED;
				$this->_transaction->updateAttributes(['status' => $status]);
			}

			return $transferred;
		}

		return FALSE;
	}

	/**
	 * @param WalletSub $target
	 *
	 * @return bool
	 */
	public function allin($target){
		$wallet            = Wallet::my();
		$this->amount      = floor($wallet->balance);
		$this->from_wallet = self::MAIN_WALLET;
		$this->to_wallet   = $target->product_code;

		return $this->transfer($wallet, $target);
	}

	/**
	 * @param \modules\wallet\models\Wallet|\modules\wallet\models\WalletSub $from_wallet
	 * @param \modules\wallet\models\Wallet|\modules\wallet\models\WalletSub $to_wallet
	 *
	 * @return bool
	 */
	private function transfer($from_wallet, $to_wallet){
		if (!empty($to_wallet) && !empty($from_wallet)){
			$transaction = $this->log([
				'wallet_id'     => $from_wallet->id,
				'wallet_sub_id' => $from_wallet->id,
				'amount'        => $this->amount,
				'balance'       => $from_wallet->balance,
				'type'          => Transaction::TYPE_TRANSFER,
				'status'        => Transaction::STATUS_PROCESSING
			], TRUE);

			if (empty($transaction)){
				return NULL;
			}

			$this->_transaction = $transaction;

			$db_transaction = Yii::$app->db->beginTransaction();
			try{
				$from_wallet->balance -= $this->amount;
				if ($from_wallet->save()){
					if ($from_wallet instanceof WalletSub){
						// Withdraw from API if from wallet is sub wallet
						$withdraw = WalletAPI::transfer([
							'player_id'      => $this->wallet->customer->username ?? NULL,
							'product_code'   => $this->from_wallet,
							'transaction_id' => $transaction->transaction_id,
							'amount'         => $this->amount * - 1
						]);

						if (empty($withdraw)){
							throw new Exception("Cannot withdraw from IM");
						}
					}

					$to_wallet->balance += $this->amount * $this->_promotion_rate;

					if ($to_wallet instanceof WalletSub && !empty($this->_promotion) && $this->_promotion->willLocked()){
						$to_wallet->status = WalletSub::STATUS_LOCKED;
					}

					if ($to_wallet->save()){
						$this->log([
							'wallet_id'     => $to_wallet->id,
							'wallet_sub_id' => $to_wallet->id,
							'amount'        => $this->amount * $this->_promotion_rate,
							'balance'       => $to_wallet->balance,
							'type'          => Transaction::TYPE_RECEIVE,
							'reference_id'  => $transaction->transaction_id,
							'status'        => Transaction::STATUS_SUCCESS
						]);

						// Store history of transfer to DB
						$history = new TransferHistory([
							'transaction_id' => $transaction->transaction_id,
							'from'           => $this->from_wallet == self::MAIN_WALLET ? NULL : $from_wallet->id,
							'to'             => $this->to_wallet == self::MAIN_WALLET ? NULL : $to_wallet->id,
							'amount'         => $this->amount,
							'customer_id'    => $this->wallet->customer_id ?? NULL
						]);

						$history->save(FALSE);

						$db_transaction->commit();

						if ($to_wallet instanceof WalletSub){
							// Transfer to API if to wallet is sub wallet
							$retry = 0;
							do{
								$transfer = WalletAPI::transfer([
									'player_id'      => $this->wallet->customer->username ?? NULL,
									'product_code'   => $this->to_wallet,
									'transaction_id' => $transaction->transaction_id . '-' . $retry,
									'amount' => $this->amount * $this->_promotion_rate
								]);

								$retry ++;
							}while (empty($transfer) && $retry < 3);


							if (empty($transfer)){
								// return money to main-wallet
								$this->returnMoney($to_wallet, $transaction->transaction_id);
							}

						}else{
							$transfer = TRUE;
						}

						if ($transfer){
							return TRUE;
						}
					}
				}
			}catch (Throwable|Exception $exception){
				Yii::error($exception, self::class);

				$db_transaction->rollBack();
			}
		}

		return FALSE;
	}

	/**
	 * Return money if having error from api
	 *
	 * @param \modules\wallet\models\WalletSub $toWallet
	 * @param string $txn_id
	 *
	 * @return void
	 */
	private function returnMoney(WalletSub $toWallet, $txn_id){
		$db_transaction = Yii::$app->db->beginTransaction();
		try{
			$toWallet->balance -= $this->amount * $this->_promotion_rate;
			$toWallet->status  = WalletSub::STATUS_ACTIVE;
			if ($toWallet->save()){
				$main_wallet          = $toWallet->wallet;
				$main_wallet->balance += $this->amount;
				$main_wallet->save();

				Transaction::updateAll(['status' => Transaction::STATUS_FAILED],
					["OR", ['transaction_id' => $txn_id], ['reference_id' => $txn_id]]);
				WalletSubTransaction::updateAll(['status' => Transaction::STATUS_FAILED],
					["OR", ['transaction_id' => $txn_id], ['reference_id' => $txn_id]]);
				TransferHistory::deleteAll(['transaction_id' => $txn_id]);

				$db_transaction->commit();
			}
		}catch (Throwable|Exception $exception){
			$db_transaction->rollBack();
		}
	}

	private $_from_wallet = NULL;

	/**
	 * @return array|\modules\wallet\models\Wallet|\modules\wallet\models\WalletSub|\yii\db\ActiveRecord
	 */
	private function findFromWallet(){
		if ($this->_from_wallet === NULL){
			if ($this->from_wallet == self::MAIN_WALLET){
				$wallet = Wallet::my();
			}else{
				$wallet = WalletSub::find()
				                   ->andWhere(['wallet_id' => $this->wallet->id, 'product_code' => $this->from_wallet])
				                   ->one();
			}

			$this->_from_wallet = $wallet ?? [];
		}

		return $this->_from_wallet;
	}

	/**
	 * @return array|\modules\wallet\models\Wallet|\modules\wallet\models\WalletSub|\yii\db\ActiveRecord|null
	 * @throws \yii\base\Exception
	 */
	private function findToWallet(){
		if ($this->to_wallet == self::MAIN_WALLET){
			$wallet = Wallet::my();
		}else{
			$wallet = WalletSub::find()
			                   ->andWhere(['wallet_id' => $this->wallet->id, 'product_code' => $this->to_wallet])
			                   ->one();

			if (empty($wallet)){
				$wallet = new WalletSub([
					'wallet_id'    => $this->wallet->id,
					'player_id'    => $this->wallet->customer_id,
					'product_code' => $this->to_wallet,
					'balance'      => 0,
					'verify_hash'  => Yii::$app->security->generatePasswordHash('0')
				]);

				if (!$wallet->save()){
					return NULL;
				}
			}
		}

		return $wallet;
	}

	/**
	 * @param $data
	 * @param $from
	 *
	 * @return Transaction|WalletSubTransaction|null
	 */
	private function log($data, $from = FALSE){
		/**@var \modules\wallet\models\Transaction|\modules\wallet\models\WalletSubTransaction $model */
		$model_class = $this->logClass($from);
		$model       = new $model_class;

		return $model::store($data);
	}

	/**
	 * @param bool $from
	 *
	 * @return string
	 */
	private function logClass($from = FALSE)
	: string{
		if ($from){
			if ($this->from_wallet == self::MAIN_WALLET){
				return Transaction::class;
			}

			return WalletSubTransaction::class;
		}

		if ($this->to_wallet == self::MAIN_WALLET){
			return Transaction::class;
		}

		return WalletSubTransaction::class;
	}

	private $_turnovers = NULL;

	/**
	 * @return array
	 */
	public function getTurnovers(){
		if ($this->_turnovers === NULL){
			$this->_turnovers = Turnover::find()
			                            ->select(['total' => 'SUM(turnover)', 'wallet_code'])
			                            ->andWhere(['player_id' => Yii::$app->user->identity->username ?? NULL])
			                            ->andWhere(new Expression("YEARWEEK(NOW(), 1) = YEARWEEK(date, 1)"))
			                            ->groupBy('wallet_code')
			                            ->indexBy('wallet_code')
			                            ->column() ?? [];
		}

		return $this->_turnovers;
	}

	/**
	 * @return float|int
	 */
	public function getTotalTurnovers(){
		if ($turnovers = $this->turnovers){
			return array_sum($turnovers);
		}

		return 0;
	}
}