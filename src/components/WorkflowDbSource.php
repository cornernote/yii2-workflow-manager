<?php
namespace cornernote\workflow\manager\components;

use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\StatusInterface;
use raoul2000\workflow\base\Transition;
use raoul2000\workflow\base\TransitionInterface;
use raoul2000\workflow\base\Workflow;
use raoul2000\workflow\base\WorkflowException;
use raoul2000\workflow\base\WorkflowInterface;
use raoul2000\workflow\source\IWorkflowSource;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\helpers\Inflector;

/**
 * WorkflowDbSource component is dedicated to read workflow definition from DB.
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
    /**
     *
     */
    const SEPARATOR_STATUS_NAME = '/';
    /**
     * @var Workflow[] list of workflow instances indexed by workflow id
     */
    private $_w = [];
    /**
     * @var Status[] list status instances indexed by their id
     */
    private $_s = [];
    /**
     * @var bool
     */
    private $_allStatusLoaded = false;
    /**
     * @var Transition[] list of out-going Transition instances indexed by the start status id
     */
    private $_t = [];

    /**
     * @param mixed $id
     * @param null $model
     * @return Status|StatusInterface
     * @throws WorkflowException
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
                'label' => $statusModel->label ? $statusModel->label : Inflector::camel2words($stId, true),
                'source' => $this
            ]);
        }
        return $this->_s[$canonicalStId];
    }

    /**
     * @param string $workflowId
     * @return Status[]|StatusInterface[]
     */
    public function getAllStatuses($workflowId)
    {
        if (!$this->_allStatusLoaded) {
            $loadedStatusIds = array_keys($this->_s);
            $statusModels = \cornernote\workflow\manager\models\Status::find()
                ->where(['workflow_id' => $workflowId])
                ->andWhere(['NOT IN', 'id', $loadedStatusIds])
                ->all();
            foreach ($statusModels as $status) {
                $canonicalStId = $workflowId . self::SEPARATOR_STATUS_NAME . $status->id;
                $this->_s[$canonicalStId] = Yii::createObject([
                    'class' => 'raoul2000\workflow\base\Status',
                    'id' => $canonicalStId,
                    'workflowId' => $workflowId,
                    'label' => $status->label ? $status->label : Inflector::camel2words($status->id, true),
                    'source' => $this
                ]);
            }
            $this->_allStatusLoaded = true;
        }
        return $this->_s;
    }

    /**
     * @param mixed $statusId
     * @param null $model
     * @return Transition|TransitionInterface[]
     * @throws WorkflowException
     */
    public function getTransitions($statusId, $model = null)
    {
        list($wId, $stId) = $this->parseStatusId($statusId);
        if (!array_key_exists($statusId, $this->_t)) {
            $transInstance = [];
            $transitions = \cornernote\workflow\manager\models\Transition::findAll([
                'workflow_id' => $wId,
                'start_status_id' => $stId,
            ]);
            foreach ($transitions as $transition) {
                // TODO : implement transition class map
                $endId = $wId . self::SEPARATOR_STATUS_NAME . $transition->end_status_id;
                $transInstance[] = Yii::createObject([
                    'class' => 'raoul2000\workflow\base\Transition',
                    'start' => $this->getStatus($statusId),
                    'end' => $this->getStatus($endId),
                    'source' => $this
                ]);
            }
            $this->_t[$statusId] = $transInstance;
        }
        return $this->_t[$statusId];
    }

    /**
     * @param string $startId
     * @param string $endId
     * @param null $defaultWorkflowId
     * @return null|TransitionInterface
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
     * @param mixed $id
     * @return Workflow|WorkflowInterface
     * @throws WorkflowException
     */
    public function getWorkflow($id)
    {
        // TODO : validate that initial status is valid
        // TODO : implement status class map
        if (!array_key_exists($id, $this->_w)) {
            $workflowModel = \cornernote\workflow\manager\models\Workflow::findOne(['id' => $id]);
            if (!$workflowModel) {
                throw new WorkflowException('No workflow found with id ' . $id);
            }
            $this->_w[$id] = Yii::createObject([
                'class' => 'raoul2000\workflow\base\Workflow',
                'id' => $workflowModel->id,
                'initialStatusId' => $workflowModel->id.self::SEPARATOR_STATUS_NAME.$workflowModel->initial_status_id,
                'source' => $this
            ]);
        }
        return $this->_w[$id];
    }

    /**
     * @param string $val canonical id (e.g. myWorkflow/myStatus)
     * @return array
     */
    public function parseStatusId($val)
    {
        // TODO : validate $val and once split in workflow_id and status_id - ensure they are both valid
        $tokens = array_map('trim', explode(self::SEPARATOR_STATUS_NAME, $val));
        return $tokens;
    }
}