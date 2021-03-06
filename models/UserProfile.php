<?php

namespace app\models;

use Yii;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * This is the model class for table "user_profile".
 *
 * @property integer $user_id
 * @property string $full_name
 * @property string $photo
 * @property string $birth_day
 */
class UserProfile extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_profile';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['birth_day', 'photo'], 'safe',
            ],

            ['birth_day', 'date', 'format' => 'php:Y-m-d'],

            ['full_name', 'string', 'max' => 40],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User'),
            'full_name' => Yii::t('app', 'Full Name'),
            'photo' => Yii::t('app', 'Photo'),
            'birth_day' => Yii::t('app', 'Birth Day'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => 'rkit\filemanager\behaviors\FileBehavior',
                'attributes' => [
                    'photo' => [
                        'storage' => 'rkit\filemanager\storages\LocalStorage',
                        'saveFilePath' => true,
                        'rules' => [
                            'imageSize' => ['minWidth' => 300, 'minHeight' => 300],
                            'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg'],
                            'extensions' => ['jpg', 'jpeg', 'png'],
                            'maxSize' => 1024 * 1024 * 1, // 1 MB
                            'tooBig' => Yii::t('app.validators', 'File size must not exceed') . ' 1Mb'
                        ],
                        'preset' => [
                            '1000x1000' => function ($realPath, $publicPath) {
                                Image::make($realPath . $publicPath)
                                    ->resize(1000, 1000, function ($constraint) {
                                        $constraint->aspectRatio();
                                        $constraint->upsize();
                                    })
                                    ->save(null, 100);
                            },
                        ],
                        'applyPresetAfterUpload' => ['1000x1000']
                    ],
                ]
            ]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
