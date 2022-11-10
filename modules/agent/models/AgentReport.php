<?php

namespace modules\agent\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%agent_report}}".
 *
 * @property int $id
 * @property int $agent_id
 * @property string $date
 * @property int $active_users
 * @property int $total_invests
 * @property double $profits
 * @property double $commissions
 * @property int $reported_at
 */
class AgentReport extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%agent_report}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['agent_id', 'date'], 'required'],
			[['agent_id', 'active_users', 'total_invests', 'reported_at'], 'integer'],
			[['date'], 'safe'],
			[['profits', 'commissions'], 'number'],
			[['agent_id', 'date'], 'unique', 'targetAttribute' => ['agent_id', 'date']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'            => Yii::t('agent', 'ID'),
			'agent_id'      => Yii::t('agent', 'Agent ID'),
			'date'          => Yii::t('agent', 'Date'),
			'active_users'  => Yii::t('agent', 'Active Users'),
			'total_invests' => Yii::t('agent', 'Total Invests'),
			'profits'       => Yii::t('agent', 'Profits'),
			'commissions'   => Yii::t('agent', 'Commissions'),
			'reported_at'   => Yii::t('agent', 'Reported At'),
		];
	}

	/**
	 * @param $agent_id
	 * @param $date
	 * @param $active_users
	 * @param $profits
	 * @param $commissions
	 *
	 * @return int
	 */
	public static function upsert(
		$agent_id,
		$date,
		$active_users,
		$profits,
		$commissions){
		return Yii::$app->db->createCommand()->upsert(self::tableName(), [
			'agent_id'      => $agent_id,
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
