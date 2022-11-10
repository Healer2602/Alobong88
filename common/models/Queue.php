<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%queue}}".
 *
 * @property int $id
 * @property string $channel
 * @property string $job
 * @property int $pushed_at
 * @property int $ttr
 * @property int $delay
 * @property int $priority
 * @property int $reserved_at
 * @property int $attempt
 * @property int $done_at
 */
class Queue extends ActiveRecord{

	public $total = 0;
	public $status;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%queue}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['channel', 'job', 'pushed_at', 'ttr', 'delay'], 'required'],
			[['channel', 'job'], 'string'],
			[['pushed_at', 'ttr', 'delay', 'priority', 'reserved_at', 'attempt', 'done_at'], 'integer'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'          => Yii::t('common', 'ID'),
			'channel'     => Yii::t('common', 'Channel'),
			'job'         => Yii::t('common', 'Job'),
			'pushed_at'   => Yii::t('common', 'Pushed At'),
			'ttr'         => Yii::t('common', 'Ttr'),
			'delay'       => Yii::t('common', 'Delay'),
			'priority'    => Yii::t('common', 'Priority'),
			'reserved_at' => Yii::t('common', 'Reserved At'),
			'attempt'     => Yii::t('common', 'Attempt'),
			'done_at'     => Yii::t('common', 'Done At'),
		];
	}
}
