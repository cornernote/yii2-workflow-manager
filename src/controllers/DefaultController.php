<?php

namespace cornernote\workflow\manager\controllers;

use cornernote\workflow\manager\models\Status;
use cornernote\workflow\manager\models\Transition;
use cornernote\workflow\manager\models\Workflow;
use Yii;
use yii\web\Controller;
use yii\web\HttpException;

/**
 * Class DefaultController
 * @package cornernote\workflow\manager\controllers
 */
class DefaultController extends Controller
{
    /**
     * Lists all Workflow models.
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Displays a single Workflow model.
     * @param string $id
     * @return \yii\web\Response
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // save transitions
        if (isset($_POST['Status'])) {
            foreach ($_POST['Status'] as $start_status_id => $statuses) {
                foreach ($statuses as $end_status_id => $checked) {
                    $transition = Transition::findOne(['workflow_id' => $model->id, 'start_status_id' => $start_status_id, 'end_status_id' => $end_status_id]);
                    if ($checked) {
                        if (!$transition) {
                            $transition = new Transition();
                            $transition->workflow_id = $model->id;
                            $transition->start_status_id = $start_status_id;
                            $transition->end_status_id = $end_status_id;
                            $transition->save();
                        }
                    } else {
                        if ($transition) {
                            $transition->delete();
                        }
                    }
                }
            }
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Workflow model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return \yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Workflow;
        if ($model->load($_POST) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * Updates an existing Workflow model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return \yii\web\Response
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load($_POST) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Updates the Initial Status of a Workflow model.
     * @param string $id
     * @param int $status_id
     * @return \yii\web\Response
     */
    public function actionInitial($id, $status_id)
    {
        $model = $this->findModel($id);
        $model->initial_status_id = $status_id;
        $model->save(false, ['initial_status_id']);
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Sets the sort order of Status models.
     * @param $id
     * @throws HttpException
     */
    public function actionSort($id)
    {
        $model = $this->findModel($id);
        if (Yii::$app->request->post('Status')) {
            foreach (Yii::$app->request->post('Status') as $k => $id) {
                $status = Status::findOne(['id' => $id, 'workflow_id' => $model->id]);
                if ($status) {
                    $status->sort_order = $k;
                    $status->save(false);
                }
            }
        }
    }

    /**
     * Deletes an existing Workflow model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Workflow model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Workflow the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Workflow::findOne($id)) !== null) {
            return $model;
        } else {
            throw new HttpException(404, Yii::t('workflow', 'The requested page does not exist.'));
        }
    }
}
