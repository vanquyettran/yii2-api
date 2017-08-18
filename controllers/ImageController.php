<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 7/30/2017
 * Time: 11:31 AM
 */

namespace common\modules\api\controllers;

use Yii;
use common\models\Image;
use yii\web\Controller;
use yii\web\UploadedFile;

class ImageController extends Controller
{
    public $modelClass = '\common\models\Image';

    public function actionFindMany($q = '', $page = 1)
    {
        /**
         * @var Image[] $images
         */

        $images = Image::find()
            ->where(['like', 'name', $q])
            ->offset($page - 1)
            ->limit(30)
            ->orderBy('create_time desc')
            ->allActive();

        $result = [
            'items' => [],
            'total_count' => Image::find()
                ->where(['like', 'name', $q])
                ->countActive()
        ];

        foreach ($images as $image) {
            $result['items'][] = [
                'id' => $image->id,
                'name' => $image->name,
                'width' => $image->width,
                'height' => $image->height,
                'aspect_ratio' => $image->aspect_ratio,
                'source' => $image->getSource(),
            ];
        }

        return json_encode($result);
    }

    public function actionFindOne()
    {
        $id = Yii::$app->request->getBodyParam('id');
        $image = Image::findOne($id);
        if ($image) {
            return json_encode([
                'id' => $image->id,
                'name' => $image->name,
                'width' => $image->width,
                'height' => $image->height,
                'aspect_ratio' => $image->aspect_ratio,
                'source' => $image->getSource(),
            ]);
        }
        return null;
    }

    public function actionUploadOne()
    {
        $module = Yii::$app->getModule('image');
        $file = UploadedFile::getInstanceByName('image_file');
        $image = new Image();
        $image->active = 1;
        $image->quality = 60;
        $image->image_name_to_basename = true;
        $image->input_resize_keys = $module->params['input_resize_keys'];
        if ($image->saveFileAndModel($file)) {
            return json_encode(['success' => true, 'image' => [
                'id' => $image->id,
                'name' => $image->name,
                'width' => $image->width,
                'height' => $image->height,
                'aspect_ratio' => $image->aspect_ratio,
                'source' => $image->getSource()
            ]]);
        } else {
            return json_encode(['success' => false, 'errors' => $image->getErrors()]);
        }
    }
}