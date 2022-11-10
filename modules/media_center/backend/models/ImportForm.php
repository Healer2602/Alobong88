<?php

namespace modules\media_center\backend\models;

use common\base\AppHelper;
use common\base\Queue;
use modules\media_center\base\ImportHelper;
use modules\media_center\base\ImportJob;
use modules\media_center\models\ImportLog;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * Import Form
 *
 * @property-read array $importers
 */
class ImportForm extends Model{

	/**
	 * @var string
	 */
	public $importer;

	/**
	 * @var \yii\web\UploadedFile
	 */
	public $file;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			[['importer'], 'string'],
			[['importer'], 'validateImporter'],
			[['importer'], 'required'],
			[['file'], 'file', 'extensions' => ['xlsx', 'csv', 'txt', 'zip'], 'skipOnEmpty' => FALSE],
		];
	}

	/**
	 * @param $attribute
	 *
	 * @return void
	 */
	public function validateImporter($attribute){
		if (!ArrayHelper::keyExists($this->{$attribute}, ImportHelper::list())){
			$this->addError($attribute, Yii::t('media_center', 'Importer does not exists.'));
		}
	}

	/**
	 * @return array
	 */
	public function attributeHints(){
		return [
			'importer' => Yii::t('media_center',
				'Download template <a href="javascript:" id="download-import-template">click here</a>')
		];
	}

	/**
	 * @return array
	 */
	public function getImporters(){
		return ImportHelper::list();
	}

	/**
	 * @return bool
	 */
	public function import(){
		if ($this->validate()){
			// Upload File first
			$file_name = Inflector::slug($this->file->baseName) . '.' . $this->file->extension;
			$file_path = AppHelper::uploadPath(Inflector::slug($this->importer), $file_name, TRUE);

			if ($this->file->saveAs($file_path)){
				$model = new ImportLog([
					'importer' => $this->importer,
					'filename' => $file_path
				]);

				if ($model->save()){
					/**@var \common\base\Queue $queue */
					$queue          = Yii::$app->queue;
					$queue->channel = Queue::CHANNEL_FILE;

					return $queue->push(new ImportJob([
						'import_id' => $model->id
					]));
				}
			}
		}

		return FALSE;
	}
}