<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use app\helpers\Util;
use app\components\BaseController;
use app\models\User;
use app\models\UserProfile;
use app\modules\admin\models\search\UserSearch;
use app\modules\admin\models\forms\UserForm;

class UsersController extends BaseController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'set-active' => ['post'],
                    'set-block' => ['post'],
                    'delete' => ['post'],
                    'operations' => ['post'],
                    'photo-upload' => ['post'],
                    'autocomplete' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'operations' => [
                'class' => 'app\modules\admin\controllers\common\OperationsAction',
                'modelName' => 'app\models\User',
                'operations' => [
                    'delete' => [],
                    'set-active' => ['status' => User::STATUS_ACTIVE],
                    'set-block' => ['status' => User::STATUS_BLOCKED]
                ]
            ],
            'set-active' => [
                'class' => 'app\modules\admin\controllers\common\UpdateAttributesAction',
                'modelName' => 'app\models\User',
                'attributes' => ['status' => User::STATUS_ACTIVE],
            ],
            'set-block' => [
                'class' => 'app\modules\admin\controllers\common\UpdateAttributesAction',
                'modelName' => 'app\models\User',
                'attributes' => ['status' => User::STATUS_BLOCKED],
            ],
            'delete' => [
                'class' => 'app\modules\admin\controllers\common\DeleteAction',
                'modelName' => 'app\models\User',
            ],
            'photo-upload' => [
                'class'     => 'rkit\filemanager\actions\UploadAction',
                'modelName' => 'app\models\UserProfile',
                'attribute' => 'photo',
                'inputName' => 'file',
                'type'      => 'image',
            ],
        ];
    }

    public function actionIndex()
    {
        $userSearch = new UserSearch();
        $dataProvider = $userSearch->search(Yii::$app->request->get());
        $statuses = User::getStatuses();

        return $this->render('index', [
            'userSearch' => $userSearch,
            'dataProvider' => $dataProvider,
            'statuses' => $statuses,
            'roles' => Yii::$app->authManager->getRoles()
        ]);
    }

    public function actionEdit($id = null)
    {
        $model = new UserForm();

        if ($id) {
            $model = $this->loadModel($model, $id);
        }

        if (Yii::$app->request->isPost) {
            if ($model->isNewRecord) {
                $model->setConfirmed();
            }

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $this->assignRole($model);

                Yii::$app->session->setFlash('success', Yii::t('app.messages', 'Saved successfully'));
                $urlToModel = Url::toRoute(['edit', 'id' => $model->id]);
                if (Yii::$app->request->isAjax) {
                    return $this->response(['redirect' => $urlToModel]);
                }
                return $this->redirect($urlToModel);
            }
            if (Yii::$app->request->isAjax) {
                return $this->response(Util::collectModelErrors($model));
            }
        }

        return $this->render('edit', [
            'model' => $model,
            'roles' => Yii::$app->authManager->getRoles()
        ]);
    }

    public function actionProfile($id)
    {
        $model = $this->loadModel(new UserProfile(), $id);

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app.messages', 'Saved successfully'));
                $urlToModel = Url::toRoute(['profile', 'id' => $model->user_id]);
                if (Yii::$app->request->isAjax) {
                    return $this->response(['redirect' => $urlToModel]);
                }
                return $this->redirect($urlToModel);
            }
            if (Yii::$app->request->isAjax) {
                return $this->response(Util::collectModelErrors($model));
            }
        }

        return $this->render('profile', [
            'model' => $model
        ]);
    }

    public function actionAutocomplete()
    {
        $result = [];
        if (($term = Yii::$app->request->post('term')) !== null) {
            $data = User::find()
                ->like('username', $term)
                ->asArray()
                ->limit(10)
                ->all();

            foreach ($data as $item) {
                $result[] = [
                    'text' => $item['username'],
                    'id' => $item['id']
                ];
            }
        }
        return $this->response($result);
    }

    private function assignRole($model)
    {
        $auth = Yii::$app->authManager;
        $auth->revokeAll($model->id);

        if (!empty($model->role)) {
            $role = $auth->getRole($model->role);
            if ($role) {
                $auth->assign($role, $model->id);
            }
        }
    }
}
