<?php

namespace modules\wallet\models;

use common\base\Status;
use common\models\BaseActiveRecord;
use modules\customer\models\Customer;
use modules\customer\models\CustomerBank;
use modules\customer\models\Setting;
use Yii;
use yii\caching\DbDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "z_bank".
 *
 * @property int $id
 * @property string $name
 * @property string $logo
 * @property string $currency_code
 * @property int $status
 *
 * @property array $currencies
 * @property array $customers
 * @property CustomerBank $customer
 */
class Bank extends BaseActiveRecord{

	public static $alias = 'bank';

	private $_currencies = NULL;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%bank}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['currency_code', 'name'], 'required'],
			[['status'], 'integer'],
			[['logo'], 'string'],
			[['name'], 'string', 'max' => 255],
			[['currency_code'], 'string', 'max' => 3],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'            => Yii::t('common', 'ID'),
			'name'          => Yii::t('common', 'Name'),
			'logo'          => Yii::t('common', 'Logo'),
			'currency_code' => Yii::t('common', 'Currency Code'),
			'status'        => Yii::t('common', 'Status'),
		];
	}

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
	 * @return \yii\db\ActiveQuery
	 */
	public function getCustomers(){
		return $this->hasMany(CustomerBank::class, ['bank_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getCustomer(){
		return $this->hasMany(Customer::class, ['id' => 'customer_id'])
		            ->viaTable(CustomerBank::tableName(), ['bank_id' => 'id']);
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public static function list($group = FALSE){
		$data = self::getDb()->cache(function (){
				return self::find()->select(['id', 'name', 'currency_code'])
				           ->andWhere(['status' => Status::STATUS_ACTIVE])
				           ->orderBy(['currency_code' => SORT_ASC, 'name' => SORT_ASC])
				           ->asArray()
				           ->all();
			}, 0, new DbDependency([
				'sql' => 'SELECT MAX(updated_at) * COUNT(*) from ' . self::tableName()
			])) ?? [];

		return ArrayHelper::map($data, 'id', 'name', $group ? 'currency_code' : NULL);
	}
}