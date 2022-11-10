<?php

namespace modules\customer\models;

use common\base\AppHelper;
use common\base\Encryption;
use common\base\Status;
use common\models\BaseActiveRecord;
use Exception;
use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%customer_kyc}}".
 *
 * @property int $id
 * @property int $customer_id
 * @property string $front_image
 * @property string $back_image
 * @property string $reason
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 *
 * @property string $statusHtml
 * @property array $statuses
 * @property Customer $customer
 * @property-read string $frontImage
 * @property-read string $backImage
 * @property-read string $name
 * @property-read boolean $approved
 */
class Kyc extends BaseActiveRecord{

	public static $alias = 'kyc';

	const SCENARIO_REJECT = 'SCENARIO_REJECT';

	const STATUS_REJECTED = - 10;

	const STATUS_PENDING = 0;

	const STATUS_APPROVED = 10;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%customer_kyc}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'customer_id'], 'integer'],
			[['front_image', 'back_image', 'reason'], 'string'],
			['reason', 'required', 'on' => self::SCENARIO_REJECT],
			['reason', 'string', 'max' => 255, 'on' => self::SCENARIO_REJECT],
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = parent::behaviors();
		unset($behaviors['status'], $behaviors['language']);
		$behaviors['audit']['module']   = Yii::t('customer', 'Player');
		$behaviors['audit']['category'] = Yii::t('customer', 'eKYC');

		return $behaviors;
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'          => Yii::t('customer', 'ID'),
			'customer_id' => Yii::t('customer', 'Player ID'),
			'front_image' => Yii::t('customer', 'Front Image'),
			'back_image'  => Yii::t('customer', 'Back Image'),
			'created_at'  => Yii::t('customer', 'Submitted At'),
			'name'        => Yii::t('customer', 'Player Name'),
			'reason'      => Yii::t('customer', 'Reason'),

			'status'     => Yii::t('common', 'Status'),
			'created_by' => Yii::t('common', 'Created By'),
			'updated_by' => Yii::t('common', 'Updated By'),
			'updated_at' => Yii::t('common', 'Updated At'),
		];
	}

	/**
	 * @return array
	 */
	public function attributeHints(){
		return [
			'reason' => Yii::t('customer', 'Enter the reason for rejecting the eKYC'),
		];
	}

	/**
	 * @return bool
	 */
	public function beforeDelete(){
		$this->deleteImage($this->front_image);
		$this->deleteImage($this->back_image);

		return parent::beforeDelete();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCustomer(){
		return $this->hasOne(Customer::class, ['id' => 'customer_id']);
	}

	/**
	 * Store NRIC/FIN/Passport images to
	 */
	public function storeImages(){
		if (!empty($this->front_image)){
			$front_image       = Encryption::encrypt($this->front_image);
			$this->front_image = md5($this->customer_id) . time() . '-1.txt';
			file_put_contents(AppHelper::uploadPath(self::$alias, $this->front_image, TRUE),
				$front_image);
		}

		if (!empty($this->back_image)){
			$back_image       = Encryption::encrypt($this->back_image);
			$this->back_image = md5($this->customer_id) . time() . '-2.txt';
			file_put_contents(AppHelper::uploadPath(self::$alias, $this->back_image, TRUE),
				$back_image);
		}
	}

	/**
	 * @return string|null
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getFrontImage(){
		return $this->decodeImage($this->front_image);
	}

	/**
	 * @return string|null
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getBackImage(){
		return $this->decodeImage($this->back_image);
	}

	/**
	 * @param $image
	 *
	 * @return string|null
	 * @throws \yii\base\InvalidConfigException
	 */
	public function decodeImage($image){
		$image_path = AppHelper::uploadPath(self::$alias, $image, TRUE);
		if (file_exists($image_path) && is_file($image_path) && ($content = file_get_contents($image_path))){
			$image = Encryption::decrypt($content);
			try{
				$image_info = getimagesizefromstring(base64_decode($image));
			}catch (Exception $exception){
				$image_info = NULL;
			}
			$mime = $image_info['mime'] ?? 'image/png';

			return "data:{$mime};base64,{$image}";
		}

		return NULL;
	}

	/**
	 * @return array
	 */
	public function getStatuses(){
		return self::listStatuses();
	}

	/**
	 * @return array
	 */
	public static function listStatuses(){
		return [
			Status::STATUS_ALL    => Yii::t('common', 'All'),
			self::STATUS_APPROVED => Yii::t('customer', 'Approved'),
			self::STATUS_PENDING  => Yii::t('customer', 'Pending'),
			self::STATUS_REJECTED => Yii::t('customer', 'Rejected'),
		];
	}

	/**
	 *
	 * @return mixed|string
	 */
	public function getStatusHtml(){
		$label_status = self::listStatuses()[$this->status];
		$badge_class  = '';
		if ($this->status == self::STATUS_APPROVED){
			$badge_class = 'badge-success';
		}elseif ($this->status == self::STATUS_REJECTED){
			$badge_class = 'badge-danger';
		}elseif ($this->status == self::STATUS_PENDING){
			$badge_class = 'badge-warning';
		}

		return Html::tag('span', $label_status,
			['class' => "px-3 py-2 badge badge-pill {$badge_class}"]);
	}

	/**
	 * @param $image
	 *
	 * @return null
	 */
	public function deleteImage($image){
		$image = AppHelper::uploadPath(self::$alias, $image, TRUE);
		if (file_exists($image)){
			AppHelper::deleteFile($image);
		}
	}

	/**
	 * @param $message
	 *
	 * @return bool
	 */
	public function mailReject($message){
		$user = $this->customer;
		if ($user){
			return Notification::rejectKyc($user, $message);
		}

		return FALSE;
	}

	/**
	 *
	 * @return bool
	 */
	public function mailApprove(){
		$user = $this->customer;
		if ($user){
			return Notification::approveKyc($user);
		}

		return FALSE;
	}

	/**
	 *
	 * @return bool
	 */
	public function mailSubmitNotify(){
		$user = $this->customer;
		if ($user){
			return Notification::submitNotifyKyc($user);
		}

		return FALSE;
	}

	/**
	 * @return string
	 */
	public function getName(){
		return Yii::t('customer', 'Player: {0}', [$this->customer->name ?? '']);
	}

	/**
	 * @return bool
	 */
	public function getApproved()
	: bool{
		return $this->status == self::STATUS_APPROVED;
	}
}