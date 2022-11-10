<?php

namespace modules\agent\api\models;

use modules\agent\models\Agent as BaseAgent;
use Yii;
use yii\base\Model;

/**
 * Agent model for API
 */
class Agent extends Model{

	public $name;
	public $email;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			['email', 'email'],
			[['email', 'name'], 'required'],
			['name', 'string']
		];
	}

	/**
	 * @throws \yii\base\Exception
	 */
	public function store(){
		if ($this->validate()){
			$model = new BaseAgent([
				'email' => $this->email,
				'name'  => $this->name,
				'code'  => $this->generateCode()
			]);

			if ($model->save()){
				$data         = $model->toArray();
				$data['link'] = $model->link;

				return $data;
			}

			$this->addErrors($model->errors);
		}

		return [];
	}

	/**
	 * @throws \yii\base\Exception
	 */
	private function generateCode()
	: ?string{
		do{
			$code = strtoupper(Yii::$app->security->generateRandomString(20));
			$code = substr(str_replace(['_', '-'], '', $code), 0, 4);

			$exists = BaseAgent::find()->where(['code' => $code])->exists();
			if (!empty($exists)){
				$code = NULL;
			}
		}while (empty($code));

		return $code;
	}
}