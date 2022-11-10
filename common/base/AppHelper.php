<?php

namespace common\base;

use DateTime;
use DateTimeZone;
use Exception;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

/**
 * Class AppHelper
 *
 * @package common\base
 */
class AppHelper extends BaseObject{

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public static function param2List($params = [])
	: array{
		$params = array_flip($params);
		foreach ($params as &$param){
			$param = ucwords(strtolower(str_replace("_", " ", $param)));
		}

		return $params;
	}

	/**
	 * @param string $field
	 * @param string $file_name
	 * @param bool $private
	 *
	 * @return string
	 */
	public static function uploadPath($field = '', $file_name = '', $private = FALSE){
		if ($private){
			$default_path = Yii::getAlias('@private_files');
		}else{
			$default_path = Yii::getAlias('@files');
		}

		$default_path .= '/' . rtrim($field, '/') . '/';

		if (!file_exists($default_path)){
			mkdir($default_path, 0777, TRUE);
		}

		return $default_path . $file_name;
	}

	/**
	 * @param string $field
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function uploadUri($field = '', $file_name = ''){
		return Yii::$app->params['file.public_url'] . "/$field/$file_name";
	}

	/**
	 * @param string $image
	 *
	 * @return string
	 */
	public static function base64Image($image = ''){
		$type = pathinfo($image, PATHINFO_EXTENSION);
		if (file_exists($image)){
			$data = file_get_contents($image);

			return 'data:image/' . $type . ';base64,' . base64_encode($data);
		}

		return '';
	}

	/**
	 * @param string $file_path
	 *
	 * @return bool
	 */
	public static function deleteFile($file_path = ''){
		try{
			@unlink($file_path);
		}catch (Exception $exception){
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * @param $data
	 * @param $path
	 * @param $file_name
	 *
	 * @return false|string
	 */
	public static function uploadEncodedImage($data, $path, $file_name){
		$upload_path = self::uploadPath($path, $file_name);
		$data        = base64_decode($data);

		if (file_put_contents($upload_path, $data)){
			return self::uploadUri($path, $file_name);
		}

		return FALSE;
	}

	/**
	 * @param $datetime
	 * @param $date_only
	 *
	 * @return int
	 */
	public static function parseDatetime($datetime, $date_only = FALSE){
		$format = Yii::$app->formatter->datetimeFormat;
		if ($date_only){
			$format = Yii::$app->formatter->dateFormat;
		}

		$format   = str_replace("php:", "", $format);
		$datetime = DateTime::createFromFormat($format, $datetime);

		return $datetime->getTimestamp();
	}

	/**
	 * @param $datetime
	 * @param $timezone
	 *
	 * @return int
	 * @throws \Exception
	 */
	public static function convertGMT($datetime, $timezone){
		$new_datetime = new DateTime($datetime, new DateTimeZone($timezone));
		$new_datetime->setTimezone(new DateTimeZone("UTC"));

		return $new_datetime->getTimestamp();
	}

	/**
	 * @param $date
	 * @param $format
	 *
	 * @return bool
	 */
	public static function validateDateFormat($date, $format = 'Y-m-d'){
		$datetime = DateTime::createFromFormat($format, $date);

		return $datetime && $datetime->format($format) == $date;
	}

	/**
	 * @return array
	 */
	public static function timezones(){
		$list = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

		return ArrayHelper::map($list, function ($data){
			return $data;
		}, function ($data){
			return $data;
		}, function ($data){
			$items = explode("/", $data);

			return $items[0] ?? NULL;
		});
	}

	/**
	 * @return mixed|null
	 */
	public static function homeUrl(){
		return EnvHelper::env('HOME_URL');
	}

	/**
	 * @return mixed|string|null
	 */
	public static function userIP(){
		if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
			$agent_ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		}else{
			$agent_ip = Yii::$app->request->getUserIP();
		}

		if (YII_ENV_DEV){
			return Yii::$app->params['ip'] ?? $agent_ip;
		}

		return $agent_ip;
	}

	/**
	 * @param $datetime
	 * @param false $end_date
	 *
	 * @return int
	 */
	public static function formatDatetime($datetime, $end_date = FALSE){
		$datetime = DateTime::createFromFormat('d/m/Y', $datetime);
		if ($end_date){
			$datetime->setTime(23, 59, 59);
		}else{
			$datetime->setTime(0, 0, 0);
		}

		return $datetime->getTimestamp();
	}

	/**
	 * @param string $range
	 *
	 * @return array
	 */
	public static function parseDateRange($range)
	: array{
		$data = explode("to", $range);
		if (!empty($data[0])){
			$from = self::formatDatetime(trim($data[0]));
		}
		if (!empty($data[1])){
			$to = self::formatDatetime(trim($data[1]), TRUE);
		}

		return [
			$from ?? NULL,
			$to ?? NULL,
		];
	}
}