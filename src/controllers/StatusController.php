<?php

namespace cornernote\workflow\manager\controllers;

use cornernote\workflow\manager\models\Status;
use Yii;
use yii\web\Controller;
use yii\web\HttpException;

/**
 * This is the class for controller "StatusController".
 */
class StatusController extends Controller
{

    /**
     * Creates a new Status model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param int $workflow_id
     * @return mixed
     */
    public function actionCreate($workflow_id)
    {
        $model = new Status;
        $model->workflow_id = $workflow_id;

        if ($model->load($_POST) && $model->save()) {
            return $this->redirect(['default/view', 'id' => $model->workflow_id]);
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * Updates an existing Status model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load($_POST) && $model->save()) {
            return $this->redirect(['default/view', 'id' => $model->workflow_id]);
        }
        return $this->render('update', ['model' => $model]);
    }

    /**
     * Deletes an existing Status model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->workflow->initial_status_id != $model->id) {
            $model->delete();
        }
        return $this->redirect(['default/view', 'id' => $model->workflow_id]);
    }

    /**
     * Sets the sort order of Status models.
     */
    public function actionSort()
    {
        if (Yii::$app->request->post('Status')) {
            foreach (Yii::$app->request->post('Status') as $k => $id) {
                $model = Status::findOne($id);
                $model->sort_order = $k;
                $model->save(false);
            }
        }
    }

    /**
     * Finds the Status model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Status the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Status::findOne(['id' => $id])) !== null) {
            return $model;
        } else {
            throw new HttpException(404, Yii::t('workflow', 'The requested page does not exist.'));
        }
    }

}
