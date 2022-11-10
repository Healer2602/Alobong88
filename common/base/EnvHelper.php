<?php

namespace common\base;

use Symfony\Component\Dotenv\Dotenv;
use yii\base\BaseObject;

/**
 * Env Helpers
 */
class EnvHelper extends BaseObject{

	/**
	 * @var bool|null
	 */
	private $_env_loaded = NULL;

	/**
	 * @param $config
	 */
	public function __construct($config = []){
		parent::__construct([]);

		if ($this->_env_loaded === NULL){
			$dotenv = new Dotenv();
			$dotenv->load(($config['root'] ?? '/') . '/.env');

			$this->_env_loaded = TRUE;
		}
	}

	/**
	 * @var null
	 */
	private static $_loaded = NULL;

	/**
	 * @return void
	 */
	public static function load($root){
		if (self::$_loaded === NULL){
			self::$_loaded = new EnvHelper([
				'root' => $root
			]);
		}
	}

	/**
	 * @param $key
	 * @param null $default value of the env
	 *
	 * @return mixed|null
	 */
	public static function env($key, $default = NULL){
		return $_ENV[$key] ?? $default;
	}
}