<?php
namespace cornernote\workflow\manager\components;

use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\Transition;
use raoul2000\workflow\base\Workflow;
use raoul2000\workflow\base\WorkflowException;
use raoul2000\workflow\source\IWorkflowSource;
use Yii;
use yii\base\Object;
use yii\helpers\Inflector;

/**
 * WorkflowDbSource component is dedicated to read workflow definition from DB.
 *
 * It doesn't implement many features available in the WorkflowFileSource component
 * released with yii2-workflow but can be used as a starting point to develop
 * a production ready component.
 *
 * Among missing features :
 * - Status, Transition and Workflow class mapping to allow usage of custom classes for those objects
 * - metadata
 * - short id usage (in this version canonical status ids must be used)
 * - robust test for method arguments (in particular ids)
 *
 * The underlying DB schema is also very simple and only include those columns
 * which are required by yii2-workflow.
 *
 */
class WorkflowDbSource extends Object implements IWorkflowSource
{
    const SEPARATOR_STATUS_NAME = '/';
    /**
     * @var Workflow[] list of workflow instances indexed by workflow id
     */
    private $_w = [];
    /**
     * @var Status[] list status instances indexed by their id
     */
    private $_s = [];
    private $_allStatusLoaded = false;
    /**
     * @var Transition[] list of out-going Transition instances indexed by the start status id
     */
    private $_t = [];

    /**
     * @see \raoul2000\workflow\source\IWorkflowSource::getStatus()
     * @param mixed $id
     * @param null $model
     * @return Status|\raoul2000\workflow\base\StatusInterface
     * @throws WorkflowException
     * @throws \yii\base\InvalidConfigException
     */
    public function getStatus($id, $model = null)
    {
        list($wId, $stId) = $this->parseStatusId($id);

        $canonicalStId = $wId . self::SEPARATOR_STATUS_NAME . $stId;

        // TODO : implement status class map
        if (!array_key_exists($canonicalStId, $this->_s)) {

            $statusModel = \cornernote\workflow\manager\models\Status::findOne([
                'workflow_id' => $wId,
                'id' => $stId
            ]);
            if ($statusModel == null) {
                throw new WorkflowException('No status found with id ' . $id);
            }
            $this->_s[$canonicalStId] = Yii::createObject([
                'class' => 'raoul2000\workflow\base\Status',
                'id' => $canonicalStId,
                'workflowId' => $statusModel->workflow_id,
                'label' => isset($statusModel->label) ? $statusModel->label : Inflector::camel2words($stId, true),
                'source' => $this
            ]);
        }
        return $this->_s[$canonicalStId];
    }

    /**
     * @see \raoul2000\workflow\source\IWorkflowSource::getAllStatuses()
     * @param string $workflowId
     * @return \raoul2000\workflow\base\Status[]|\raoul2000\workflow\base\StatusInterface[]
     * @throws \yii\base\InvalidConfigException
     */
    public function getAllStatuses($workflowId)
    {
        if (!$this->_allStatusLoaded) {

            $loadedStatusIds = array_keys($this->_s);

            $dbStatus = \cornernote\workflow\manager\models\Status::find()
                ->where(['workflow_id' => $workflowId])
                ->andWhere(['NOT IN', 'id', $loadedStatusIds])
                ->all();

            foreach ($dbStatus as $status) {
                $canonicalStId = $status->workflow_id . self::SEPARATOR_STATUS_NAME . $status->id;

                $this->_s[$canonicalStId] = Yii::createObject([
                    'class' => 'raoul2000\workflow\base\Status',
                    'id' => $canonicalStId,
                    'workflowId' => $status->workflow_id,
                    'label' => isset($status->label) ? $status->label : Inflector::camel2words($status->id, true),
                    'source' => $this
                ]);
            }
            $this->_allStatusLoaded = true;
        }
        return $this->_s;
    }

    /**
     * @see \raoul2000\workflow\source\IWorkflowSource::getTransitions()
     * @param mixed $startStatusId
     * @param null $model
     * @return Transition|\raoul2000\workflow\base\TransitionInterface[]
     * @throws WorkflowException
     * @throws \yii\base\InvalidConfigException
     */
    public function getTransitions($startStatusId, $model = null)
    {
        list($wId, $stId) = $this->parseStatusId($startStatusId);
        $startId = $wId . self::SEPARATOR_STATUS_NAME . $stId;

        if (!array_key_exists($startId, $this->_t)) {

            $transInstance = [];
            $transitions = \cornernote\workflow\manager\models\Transition::findAll([
                'start_status_id' => $stId,
                'start_status_workflow_id' => $wId
            ]);
            foreach ($transitions as $transition) {
                // TODO : implement transition class map
                $endId = $transition->end_status_workflow_id . self::SEPARATOR_STATUS_NAME . $transition->end_status_id;
                $transInstance[] = Yii::createObject([
                    'class' => 'raoul2000\workflow\base\Transition',
                    'start' => $this->getStatus($startId),
                    'end' => $this->getStatus($endId),
                    'source' => $this
                ]);
            }
            $this->_t[$startId] = $transInstance;
        }
        return $this->_t[$startId];
    }

    /**
     * @see \raoul2000\workflow\source\IWorkflowSource::getTransition()
     * @param string $startId
     * @param string $endId
     * @param null $defaultWorkflowId
     * @return null|\raoul2000\workflow\base\TransitionInterface
     */
    public function getTransition($startId, $endId, $defaultWorkflowId = null)
    {
        $tr = $this->getTransitions($startId, $defaultWorkflowId);
        if (count($tr) > 0) {
            foreach ($tr as $aTransition) {
                if ($aTransition->getEndStatus()->getId() == $endId) {
                    return $aTransition;
                }
            }
        }
        return null;
    }

    /**
     * @see \raoul2000\workflow\source\IWorkflowSource::getWorkflow()
     * @param mixed $id
     * @return Workflow|\raoul2000\workflow\base\WorkflowInterface
     * @throws WorkflowException
     * @throws \yii\base\InvalidConfigException
     */
    public function getWorkflow($id)
    {

        // TODO : validate that initial status is valid
        // TODO : implement status class map

        if (!array_key_exists($id, $this->_w)) {
            $workflowModel = \cornernote\workflow\manager\models\Workflow::findOne([
                'id' => $id
            ]);

            if ($workflowModel == null) {
                throw new WorkflowException('No workflow found with id ' . $id);
            }
            $initialStatusId = $workflowModel->id . self::SEPARATOR_STATUS_NAME . $workflowModel->initial_status_id;
            $this->_w[$id] = Yii::createObject([
                'class' => 'raoul2000\workflow\base\Workflow',
                'id' => $id,
                'initialStatusId' => $initialStatusId,
                'source' => $this
            ]);
        }
        return $this->_w[$id];
    }

    /**
     *
     * @param string $val canonical id (e.g. myWorkflow/myStatus)
     * @return array:
     */
    public function parseStatusId($val)
    {

        // TODO : validate $val and once splitted in workflow_id and status_id
        // ensure they are both valid

        $tokens = array_map('trim', explode(self::SEPARATOR_STATUS_NAME, $val));
        return $tokens;
    }
}