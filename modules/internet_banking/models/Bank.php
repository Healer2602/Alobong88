<?php

namespace modules\internet_banking\models;

use common\base\Status;
use modules\customer\models\Setting;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%internet_banking_bank}}".
 *
 * @property int $id
 * @property int $bank_id
 * @property string $currency_code
 * @property string $code
 * @property string $logo
 * @property string $name
 * @property string|array $content
 * @property int $status
 *
 * @property array $statuses
 * @property array $currencies
 * @property array $banks
 * @property string $visibilityLabel
 * @property string $title
 * @property string $logoUrl
 * @property \modules\wallet\models\Bank $bank
 */
class Bank extends ActiveRecord{

	public $account_id;
	public $account_name;
	public $account_branch;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%internet_banking_bank}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['bank_id', 'currency_code', 'account_id', 'account_name', 'account_branch', 'code'], 'required'],
			[['status'], 'integer'],
			[['currency_code'], 'string', 'max' => 3],
			[['code', 'name'], 'string', 'max' => 20],
			[['logo', 'account_id', 'account_name', 'account_branch'], 'string'],
			[['code'], 'unique'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'             => Yii::t('internet_banking', 'ID'),
			'bank_id'        => Yii::t('internet_banking', 'Bank'),
			'currency_code'  => Yii::t('internet_banking', 'Currency Code'),
			'code'           => Yii::t('internet_banking', 'Bank Code'),
			'logo'           => Yii::t('internet_banking', 'Logo'),
			'name'           => Yii::t('internet_banking', 'Name'),
			'account_id'     => Yii::t('internet_banking', 'Account Number'),
			'account_name'   => Yii::t('internet_banking', 'Account Name'),
			'account_branch' => Yii::t('internet_banking', 'Branch'),

			'status' => Yii::t('common', 'Status'),
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = parent::behaviors();
		unset($behaviors['language'], $behaviors['status']);

		return $behaviors;
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getBank(){
		return $this->hasOne(\modules\wallet\models\Bank::class, ['id' => 'bank_id']);
	}


	/**
	 * @return array
	 * @throws \Exception
	 */
	public function getBanks(){
		return \modules\wallet\models\Bank::list(TRUE);
	}

	/**
	 * @return array
	 */
	public function getStatuses(){
		$statuses = Status::states();
		unset($statuses[Status::STATUS_ALL]);

		return $statuses;
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if (!empty($this->account_id)){
			$this->content = Json::encode([
				'account_id'     => $this->account_id,
				'account_name'   => $this->account_name,
				'account_branch' => $this->account_branch,
			]);
		}

		if (is_array($this->content)){
			$this->content = Json::encode($this->content);
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @inheritDoc
	 */
	public function afterFind(){
		$this->content        = Json::decode($this->content);
		$this->account_id     = $this->content['account_id'] ?? '';
		$this->account_name   = $this->content['account_name'] ?? '';
		$this->account_branch = $this->content['account_branch'] ?? '';
	}

	private $_currencies = NULL;

	/**
	 * @return array
	 */
	public function getCurrencies(){
		if ($this->_currencies === NULL){
			$model = new Setting();
			$model->getValues();

			$this->_currencies = $model->listCurrency;
		}

		return $this->_currencies ?? [];
	}

	/**
	 * @return string|null
	 */
	public function getTitle(){
		if (!empty($this->name)){
			return $this->name;
		}

		return $this->bank->name ?? NULL;
	}

	/**
	 * @return string|null
	 */
	public function getLogoUrl(){
		if (!empty($this->logo)){
			return $this->logo;
		}

		return $this->bank->logo ?? NULL;
	}
}
