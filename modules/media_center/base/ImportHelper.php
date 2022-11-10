<?php

namespace modules\media_center\base;

use Symfony\Component\Yaml\Yaml;
use Yii;
use yii\base\BaseObject;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * Media Center Import Helper
 */
class ImportHelper extends BaseObject{

	/**
	 * @return array
	 */
	public static function list(){
		return ArrayHelper::getColumn(self::importers(), 'name');
	}

	/**
	 * @param $importer
	 *
	 * @return mixed|null
	 */
	public static function validator($importer){
		$validators = ArrayHelper::getColumn(self::importers(), 'validator');

		return $validators[$importer] ?? NULL;
	}

	/**
	 * @param $importer
	 *
	 * @return mixed|null
	 */
	public static function importClass($importer){
		$validators = ArrayHelper::getColumn(self::importers(), 'class');

		return $validators[$importer] ?? NULL;
	}

	/**
	 * @param $importer
	 *
	 * @return mixed|null
	 */
	public static function importTemplate($importer){
		$templates = ArrayHelper::getColumn(self::importers(), 'template');

		return $templates[$importer] ?? NULL;
	}

	/**
	 * @return array
	 */
	public static function importers(){
		return Yii::$app->cache->getOrSet(__METHOD__, function (){
			return self::findImporters();
		}, 0, new TagDependency([
			'tags' => [self::class]
		]));
	}

	/**
	 * @return array
	 */
	private static function findImporters()
	: array{
		$importers  = [];
		$bootstraps = Yii::$app->bootstrap;

		foreach ($bootstraps as $bootstrap){
			$importer = Yii::getAlias("@modules/{$bootstrap}/importer.yml");
			if (file_exists($importer)){
				$app_importers = Yaml::parse(file_get_contents($importer));
				$importers     = ArrayHelper::merge($importers, $app_importers);
			}
		}

		ksort($importers);

		return $importers;
	}
}