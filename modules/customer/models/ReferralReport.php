<?php

namespace modules\customer\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%customer_referral_report}}".
 *
 * @property int $id
 * @property int $referral_id
 * @property string $date
 * @property int $active_users
 * @property int $total_invests
 * @property double $profits
 * @property double $commissions
 * @property int $reported_at
 */
class ReferralReport extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%customer_referral_report}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['referral_id', 'date'], 'required'],
			[['referral_id', 'active_users', 'total_invests', 'reported_at'], 'integer'],
			[['date'], 'safe'],
			[['profits', 'commissions'], 'number'],
			[['referral_id', 'date'], 'unique', 'targetAttribute' => ['referral_id', 'date']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'            => Yii::t('customer', 'ID'),
			'referral_id'   => Yii::t('customer', 'Referral ID'),
			'date'          => Yii::t('customer', 'Date'),
			'active_users'  => Yii::t('customer', 'Active Users'),
			'total_invests' => Yii::t('customer', 'Total Invests'),
			'profits'       => Yii::t('customer', 'Profits'),
			'commissions'   => Yii::t('customer', 'Commissions'),
			'reported_at'   => Yii::t('customer', 'Reported At'),
		];
	}

	/**
	 * @param $referral_id
	 * @param $date
	 * @param $active_users
	 * @param $profits
	 * @param $commissions
	 *
	 * @return int
	 */
	public static function upsert(
		$referral_id,
		$date,
		$active_users,
		$profits,
		$commissions){
		return Yii::$app->db->createCommand()->upsert(self::tableName(), [
			'referral_id'   => $referral_id,
			'date'          => $date,
			'active_users'  => $active_users,
			'total_invests' => 1,
			'profits'       => $profits,
			'commissions'   => $commissions,
			'reported_at'   => time()
		], [
			'total_invests' => new Expression("total_invests + 1"),
			'profits'       => new Expression("profits + :p", [':p' => $profits]),
			'commissions'   => new Expression("commissions + :c", [':c' => $commissions]),
		])->execute();
	}
}
