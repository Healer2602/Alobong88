<?php

namespace modules\ezp\models;

use common\base\Status;
use modules\customer\models\Setting;
use Yii;
use yii\caching\DbDependency;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%bank}}".
 *
 * @property int $id
 * @property int $bank_id
 * @property string $currency_code
 * @property string $code
 * @property string $logo
 * @property string $name
 * @property int $status
 * @property int $visibility
 * @property double $maximum
 *
 * @property array $statuses
 * @property array $visibilities
 * @property array $currencies
 * @property array $banks
 * @property string $visibilityLabel
 * @property string $title
 * @property string $logoUrl
 * @property \modules\wallet\models\Bank $bank
 */
class Bank extends ActiveRecord{

	const VISIBILITY_ALL = 0;

	const VISIBILITY_TOPUP = 1;

	const VISIBILITY_WITHDRAW = 2;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%ezp_bank}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['bank_id', 'currency_code', 'visibility', 'code'], 'required'],
			[['status', 'visibility'], 'integer'],
			[['currency_code'], 'string', 'max' => 3],
			[['code'], 'string', 'max' => 20],
			[['logo', 'name'], 'string'],
			[['maximum'], 'compare', 'compareValue' => 0, 'operator' => '>='],
			['maximum', 'number'],
			[['code'], 'unique']
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'            => Yii::t('ezp', 'ID'),
			'bank_id'       => Yii::t('ezp', 'Bank'),
			'currency_code' => Yii::t('ezp', 'Currency Code'),
			'code'          => Yii::t('ezp', 'Bank Code'),
			'logo'          => Yii::t('ezp', 'Logo'),
			'name'          => Yii::t('ezp', 'Name'),
			'maximum'       => Yii::t('ezp', 'Maximum Deposit Amount'),
			'visibility'    => Yii::t('ezp', 'Visibility'),

			'status' => Yii::t('common', 'Status'),
		];
	}

	/**
	 * @return array
	 */
	public function attributeHints(){
		return [
			'maximum' => Yii::t('ezp', 'Put "0" for unlimited'),
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
	 */
	public function getStatuses(){
		$statuses = Status::states();
		unset($statuses[Status::STATUS_ALL]);

		return $statuses;
	}

	/**
	 * @return array
	 */
	public function getVisibilities(){
		return [
			self::VISIBILITY_ALL      => Yii::t('ezp', 'Deposit & Withdraw'),
			self::VISIBILITY_TOPUP    => Yii::t('ezp', 'Deposit only'),
			self::VISIBILITY_WITHDRAW => Yii::t('ezp', 'Withdraw only'),
		];
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function getBanks(){
		return \modules\wallet\models\Bank::list(TRUE);
	}

	/**
	 * @return mixed|null
	 */
	public function getVisibilityLabel(){
		return $this->visibilities[$this->visibility] ?? NULL;
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

	/**
	 * @return array
	 * @throws \Exception
	 */
	public static function list(){
		return static::getDb()->cache(function (){
			$query = static::find()
			               ->andWhere(['status' => Status::STATUS_ACTIVE])
			               ->orderBy(['code' => SORT_ASC]);

			return $query->asArray()->all();
		}, 0, new DbDependency([
			'sql' => self::find()->select(['COUNT(*) * MAX(updated_at)'])->createCommand()->rawSql
		]));
	}
}