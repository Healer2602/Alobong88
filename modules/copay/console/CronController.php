<?php

namespace modules\copay\console;

use common\base\Status;
use modules\copay\gateway\Api;
use modules\copay\models\Bank;
use modules\wallet\models\Gateway;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class CronController
 */
class CronController extends Controller{

	/**
	 * @return int
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	public function actionDepositCheck(){
		$total   = 0;
		$channel = Api::BANK_TRANSFER;
		$host    = gethostname();
		$ip      = gethostbyname($host);
		if (!empty($_SERVER['SERVER_ADDR'])){
			$ip = $_SERVER['SERVER_ADDR'];
		}

		$banks  = Bank::find()->all();
		$config = Gateway::find()
		                 ->andWhere(['key' => 'copay'])
		                 ->asArray()
		                 ->one();

		if (!empty($banks) && !empty($config)){
			$api_copay = new Api([
				'apiKey'    => $config['api_key'] ?? NULL,
				'apiSecret' => $config['api_secret'] ?? NULL,
				'apiUrl'    => $config['endpoint'] ?? NULL,
			]);

			if (!empty($api_copay)){
				foreach ($banks as $bank){
					$transaction_id = random_int(1, 9999) . $bank->id;
					if (ArrayHelper::isIn($bank->code, [Api::ZALO_PAYMENT, Api::MOMO_PAYMENT])){
						$channel = $bank->code;
					}

					$data = [
						'uid'        => $api_copay->apiKey,
						'orderid'    => $transaction_id,
						'channel'    => $channel,
						'notify_url' => Url::to(['#'], 'https'),
						'return_url' => Url::to(['#'], 'https'),
						'amount'     => 100000,
						'userip'     => $ip,
						'user_name'  => 'SYSTEM',
						'custom'     => 'CUS SYSTEM TEST',
						'timestamp'  => time(),
					];

					$sign_data = $data;
					ksort($sign_data);
					$sign_data['key'] = $api_copay->apiSecret;
					$encode_data      = urldecode(http_build_query($sign_data));
					$data['sign']     = strtoupper(md5($encode_data));

					$response = $api_copay->copayClient($data);
					if (isset($response['status'])){
						if (($response['status'] == '10000') && !empty($response['result']['payurl'])){
							if ($bank->status != Status::STATUS_ACTIVE){
								$bank->status = Status::STATUS_ACTIVE;
								$bank->save(FALSE);
								$total ++;
							}
						}elseif (ArrayHelper::isIn($response['status'], ['21113', '21114'])){
							if ($bank->status != Status::STATUS_INACTIVE){
								$bank->status = Status::STATUS_INACTIVE;
								$bank->save(FALSE);
								$total ++;
							}
						}
					}
				}
			}
		}

		$message = Yii::t('copay',
			"Total {0} banks updated.", [$total]);

		echo $message . "\n";

		return ExitCode::OK;
	}
}