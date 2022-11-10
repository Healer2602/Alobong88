<?php

namespace modules\post\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%post_category_map}}".
 *
 * @property int $post_id
 * @property int $post_category_id
 *
 * @property Category $category
 * @property Post $post
 */
class PostCategoryMap extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%post_category_map}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['post_id', 'post_category_id'], 'required'],
			[['post_id', 'post_category_id'], 'integer'],
			[['post_id', 'post_category_id'], 'unique', 'targetAttribute' => ['post_id', 'post_category_id']],
			[['post_category_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Category::class, 'targetAttribute' => ['product_category_id' => 'id']],
			[['post_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Post::class, 'targetAttribute' => ['product_data_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'post_category_id' => Yii::t('post', 'Category'),
			'post_id'          => Yii::t('post', 'Post'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCategory(){
		return $this->hasOne(Category::class, ['id' => 'post_category_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPost(){
		return $this->hasOne(Post::class, ['id' => 'post_id']);
	}
}
