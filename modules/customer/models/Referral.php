<?php

namespace modules\customer\models;

use common\base\Status;
use common\models\BaseActiveRecord;
use modules\website\models\WebsiteSetting;
use Yii;

/**
 * This is the model class for table "{{%customer_referral}}".
 *
 * @property int $id
 * @property int $customer_id
 * @property string $code
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 * @property float $commission
 * @property int $active_users
 *
 * @property-read Customer $customer
 * @property-read Customer[] $assignedCustomers
 * @property-read string $customerDetail
 * @property-read array $customers
 * @property-read string $link
 */
class Referral extends BaseActiveRecord{

	public static $alias = 'referral';

	public $total = 0;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%customer_referral}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['customer_id', 'code'], 'required'],
			[['customer_id', 'created_by', 'created_at', 'updated_by', 'updated_at', 'status'], 'integer'],
			[['code'], 'string', 'max' => 255],
			[['code'], 'unique'],
			[['customer_id'], 'unique'],
			['commission', 'number', 'min' => 0, 'max' => 100],
			['active_users', 'integer', 'min' => 0],
			['active_users', 'default', 'value' => 1],
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = parent::behaviors();
		unset($behaviors['language'], $behaviors['audit']);

		return $behaviors;
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'           => Yii::t('customer', 'ID'),
			'customer_id'  => Yii::t('customer', 'Player'),
			'code'         => Yii::t('customer', 'Referral ID'),
			'created_by'   => Yii::t('customer', 'Created By'),
			'created_at'   => Yii::t('customer', 'Created At'),
			'updated_by'   => Yii::t('customer', 'Updated By'),
			'updated_at'   => Yii::t('customer', 'Updated At'),
			'total'        => Yii::t('customer', 'Total Players'),
			'commission'   => Yii::t('customer', 'Commission'),
			'active_users' => Yii::t('customer', 'Active Users'),
		];
	}

	/**
	 * @return bool
	 */
	public function beforeValidate(){
		$this->code = $this->generateUniqueCode();

		return parent::beforeValidate();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCustomer(){
		return $this->hasOne(Customer::class, ['id' => 'customer_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getReport(){
		return $this->hasOne(ReferralReport::class, ['referral_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getReports(){
		return $this->hasMany(ReferralReport::class, ['referral_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getAssignedCustomers(){
		return $this->hasMany(Customer::class, ['referral_id' => 'id']);
	}

	/**
	 * @return string
	 */
	public function getCustomerDetail(){
		return $this->customer->name . " (" . $this->customer->email . ")";
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getCustomers(){
		$query = Customer::find()
		                 ->select(['email', 'id'])
		                 ->andWhere(['status' => Status::STATUS_ACTIVE])
		                 ->indexBy('id')
		                 ->orderBy(['email' => SORT_ASC]);

		if ($this->isNewRecord){
			$query->andWhere(['NOT', ['id' => Referral::find()->select('customer_id')]]);
		}else{
			$query->andWhere(["OR", ['id' => $this->customer_id], ['NOT', ['id' => Referral::find()
			                                                                               ->select('customer_id')]]]);
		}

		return $query->column();
	}

	/**
	 * @return string
	 */
	public function getLink(){
		$setting = new WebsiteSetting();
		$setting->getValues();

		return $setting->site_url . '/ref/' . $this->code;
	}

	/**
	 * @param $code
	 *
	 * @return null|\modules\customer\models\Referral
	 */
	public static function findByCode($code){
		return static::find()
		             ->joinWith('customer', FALSE)
		             ->andWhere(['customer.status' => Status::STATUS_ACTIVE])
		             ->andWhere(['referral.status' => Status::STATUS_ACTIVE])
		             ->andWhere(['code' => $code])
		             ->one();
	}

	/**
	 * @param int $length
	 * @param string $prefix
	 *
	 * @return false|string
	 */
	public static function randomCode($length = 6, $prefix = ''){
		$chars         = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$totalChars    = strlen($chars);
		$totalRepeat   = ceil($length / $totalChars);
		$repeatString  = str_repeat($chars, $totalRepeat);
		$shuffleString = str_shuffle($repeatString);

		return $prefix . substr($shuffleString, 1, $length);
	}

	/**
	 * @return string
	 */
	private function generateUniqueCode(){
		$i = 0;
		do{
			$i ++;
			$length = Yii::$app->params['referral']['length'];
			$prefix = Yii::$app->params['referral']['prefix'];

			$code = self::randomCode($length, $prefix);
			if (self::find()->andWhere(['code' => $code])->exists()){
				$code = NULL;
			}
		}while (empty($code) && $i < 5);

		return $code;
	}
}
