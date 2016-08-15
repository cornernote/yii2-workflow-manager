<?php

namespace cornernote\workflow\manager\controllers;

use cornernote\workflow\manager\models\form\StatusForm;
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
     * @return \yii\web\Response
     */
    public function actionCreate($workflow_id)
    {
        $model = new StatusForm();
        $model->status = new Status();
        $model->status->loadDefaultValues();
        $model->setAttributes(Yii::$app->request->post());
        $model->status->workflow_id = $workflow_id;

        if (Yii::$app->request->post() && $model->save()) {
            Yii::$app->getSession()->setFlash('success', 'Status has been created.');
            return $this->redirect(['default/view', 'id' => $model->status->workflow_id]);
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * Updates an existing Status model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @param string $workflow_id
     * @return \yii\web\Response
     */
    public function actionUpdate($id, $workflow_id)
    {
        $model = new StatusForm();
        $model->status = $this->findModel($id, $workflow_id);
        $model->setAttributes(Yii::$app->request->post());

        if (Yii::$app->request->post() && $model->save()) {
            Yii::$app->getSession()->setFlash('success', 'Status has been updated.');
            return $this->redirect(['default/view', 'id' => $model->status->workflow_id]);
        }
        return $this->render('update', ['model' => $model]);
    }

    /**
     * Deletes an existing Status model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @param string $workflow_id
     * @return \yii\web\Response
     */
    public function actionDelete($id, $workflow_id)
    {
        $model = $this->findModel($id, $workflow_id);
        if ($model->workflow->initial_status_id != $model->id) {
            $model->delete();
        }
        return $this->redirect(['default/view', 'id' => $model->workflow_id]);
    }

    /**
     * Finds the Status model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @param string $workflow_id
     * @return Status the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id, $workflow_id)
    {
        if (($model = Status::findOne(['id' => $id, 'workflow_id' => $workflow_id])) !== null) {
            return $model;
        } else {
            throw new HttpException(404, Yii::t('workflow', 'The requested page does not exist.'));
        }
    }

}
