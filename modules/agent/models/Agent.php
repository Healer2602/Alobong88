<?php

namespace modules\agent\models;

use common\base\AppHelper;
use common\base\Status;
use common\models\BaseActiveRecord;
use modules\customer\models\Customer;
use Yii;

/**
 * This is the model class for table "{{%agent}}".
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $code
 * @property int $status
 * @property double $range_1
 * @property double $range_2
 * @property double $range_3
 * @property double $deposit_rate
 * @property double $withdrawal_rate
 * @property double $administration_rate
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 *
 * @property-read string $link
 * @property-read Customer[] $customers
 * @property-read array $ranges
 */
class Agent extends BaseActiveRecord{

	const STATUS_ACTIVE = 10;

	const STATUS_PENDING = 0;

	const STATUS_REJECTED = 5;

	const STATUS_BLOCKED = - 10;

	public static $alias = 'agent';

	public $total = 0;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%agent}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['code'], 'required'],
			[['status', 'created_at', 'updated_by', 'updated_at'], 'integer'],
			[['range_1', 'range_2', 'range_3', 'active'], 'number'],
			[['name', 'email', 'code'], 'string', 'max' => 255],
			[['code'], 'unique'],
			[['email'], 'unique'],
			['status', 'default', 'value' => self::STATUS_PENDING],
			[['deposit_rate', 'withdrawal_rate', 'administration_rate'], 'number']
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = parent::behaviors();
		unset($behaviors['language'], $behaviors['status']);
		$behaviors['audit']['module']                 = Yii::t('agent', 'Agent');
		$behaviors['blameable']['createdByAttribute'] = FALSE;

		return $behaviors;
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'                  => Yii::t('agent', 'ID'),
			'name'                => Yii::t('agent', 'Name'),
			'email'               => Yii::t('agent', 'Email'),
			'code'                => Yii::t('agent', 'Code'),
			'status'              => Yii::t('agent', 'Status'),
			'range_1'             => Yii::t('agent', 'Commission Rate Range 1'),
			'range_2'             => Yii::t('agent', 'Commission Rate Range 2'),
			'range_3'             => Yii::t('agent', 'Commission Rate Range 3'),
			'created_at'          => Yii::t('agent', 'Created At'),
			'updated_by'          => Yii::t('agent', 'Updated By'),
			'updated_at'          => Yii::t('agent', 'Updated At'),
			'total'               => Yii::t('agent', 'Players'),
			'active'              => Yii::t('agent', 'Active Players'),
			'deposit_rate'        => Yii::t('agent', 'Deposit Rate'),
			'withdrawal_rate'     => Yii::t('agent', 'Withdrawal Rate'),
			'administration_rate' => Yii::t('agent', 'Administration Rate'),
		];
	}

	/**
	 * @param $insert
	 * @param $changedAttributes
	 *
	 * @return void
	 */
	public function afterSave($insert, $changedAttributes){
		if (isset($changedAttributes['status'])){
			if ($this->status == self::STATUS_ACTIVE && $changedAttributes['status'] == self::STATUS_PENDING){
				Notification::approve($this);
			}elseif ($this->status == self::STATUS_REJECTED && $changedAttributes['status'] == self::STATUS_PENDING){
				Notification::reject($this);
			}elseif ($this->status == self::STATUS_BLOCKED){
				Notification::block($this);
			}
		}

		parent::afterSave($insert, $changedAttributes);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getReports(){
		return $this->hasMany(AgentReport::class, ['agent_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCustomers(){
		return $this->hasMany(Customer::class, ['agent_id' => 'id']);
	}

	/**
	 * @return array
	 */
	public static function statuses(){
		return [
			self::STATUS_ACTIVE   => Yii::t('agent', 'Active'),
			self::STATUS_PENDING  => Yii::t('agent', 'Pending for approval'),
			self::STATUS_REJECTED => Yii::t('agent', 'Rejected'),
			self::STATUS_BLOCKED  => Yii::t('agent', 'Blocked'),
		];
	}

	/**
	 * @return string
	 */
	public function getLink(){
		return AppHelper::homeUrl() . "/ref/{$this->code}";
	}

	/**
	 * @param $code
	 *
	 * @return null|\modules\agent\models\Agent
	 */
	public static function findByCode($code){
		return static::find()
		             ->andWhere(['agent.status' => Status::STATUS_ACTIVE])
		             ->andWhere(['code' => $code])
		             ->one();
	}

	/**
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getRanges(){
		$settings = new Setting();
		$settings->getValues();

		$ranges = [];
		if (!empty($settings->range_1[1])){
			$ranges[1] = Yii::t('common', '{0}-{1}', [
				Yii::$app->formatter->asCurrency($settings->range_1[0]),
				Yii::$app->formatter->asCurrency($settings->range_1[1]),
			]);
		}

		if (!empty($settings->range_2[1])){
			$ranges[2] = Yii::t('common', '{0}-{1}', [
				Yii::$app->formatter->asCurrency($settings->range_2[0]),
				Yii::$app->formatter->asCurrency($settings->range_2[1]),
			]);
		}

		if (!empty($settings->range_3[0])){
			if (empty($settings->range_3[1])){
				$ranges[3] = Yii::t('common', 'Above {0}', [
					Yii::$app->formatter->asCurrency($settings->range_3[0])
				]);
			}else{
				$ranges[3] = Yii::t('common', '{0}-{1}', [
					Yii::$app->formatter->asCurrency($settings->range_3[0]),
					Yii::$app->formatter->asCurrency($settings->range_3[1]),
				]);
			}
		}

		return $ranges;
	}

	/**
	 * @return mixed|null
	 */
	public function getStatusHtml(){
		return self::statuses()[$this->status] ?? NULL;
	}
}