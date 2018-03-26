<?php
namespace cornernote\workflow\manager\components;

use raoul2000\workflow\base\SimpleWorkflowBehavior;
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
use yii\base\BaseObject;
use yii\caching\Cache;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;

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
class WorkflowDbSource extends BaseObject implements IWorkflowSource
{
    /**
     *    The regular expression used to validate status and workflow Ids.
     */
    const PATTERN_ID = '/^[a-zA-Z]+[[:alnum:]-]*$/';

    /**
     * The separator used to create a status id by concatenating the workflow id and
     * the status local id (e.g. post/draft).
     */
    const SEPARATOR_STATUS_NAME = '/';

    /**
     * Name of the array key for status list definition
     */
    const KEY_NODES = 'status';

    /**
     * array key for status class in class map
     */
    const TYPE_STATUS = 'status';

    /**
     * array key for transition class in class map
     */
    const TYPE_TRANSITION = 'transition';

    /**
     * array key for workflow class in class map
     */
    const TYPE_WORKFLOW = 'workflow';

    /**
     *
     * @var string|array|Cache The workflow definition cache used by this
     * source component can be be specified in one of the following forms :
     *
     * - string : ID of an existing cache component registered in the current Yii::$app.
     * - a configuration array: the array must contain a class element which is treated as the object class,
     * and the rest of the name-value pairs will be used to initialize the corresponding object properties
     * - object : the instance of the cache component
     *
     * By default no cache is used.
     */
    public $definitionCache;

    /**
     * @var array list of all workflow definition indexed by workflow id
     */
    private $_workflowDef = [];

    /**
     * @var Workflow[] list of workflow instances indexed by workflow id
     */
    private $_w = [];

    /**
     * @var Status[][] list status instances for each workflow indexed by their workflow_id and id
     */
    private $_s = [];

    /**
     * @var Transition[][] list of out-going Transition instances indexed by the start status id
     */
    private $_t = [];

    /**
     * @var object workflow definition cache
     */
    private $_dc;

    /**
     * The class map is used to allow the use of alternate classes to implement built-in types. This way
     * you can provide your own implementation for status, transition or workflow.
     * The class map can be configured when this component is created but can't be modified afterwards.
     *
     * @var array
     */
    private $_classMap = [
        self::TYPE_WORKFLOW => 'raoul2000\workflow\base\Workflow',
        self::TYPE_STATUS => 'raoul2000\workflow\base\Status',
        self::TYPE_TRANSITION => 'raoul2000\workflow\base\Transition'
    ];

    /**
     * @var bool[]
     */
    private $_allStatusLoaded = [];

    /**
     * Constructor method.
     *
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct($config = [])
    {
        if (array_key_exists('classMap', $config)) {
            if (is_array($config['classMap']) && count($config['classMap']) != 0) {
                $this->_classMap = array_merge($this->_classMap, $config['classMap']);
                unset($config['classMap']);
                // classmap validation
                foreach ([self::TYPE_STATUS, self::TYPE_TRANSITION, self::TYPE_WORKFLOW] as $type) {
                    $className = $this->getClassMapByType($type);
                    if (empty($className)) {
                        throw new InvalidConfigException("Invalid class map value : missing class for type " . $type);
                    }
                }
            } else {
                throw new InvalidConfigException("Invalid property type : 'classMap' must be a non-empty array");
            }
        }

        parent::__construct($config);
    }

    /**
     * @param mixed $id
     * @param null $model
     * @return Status|StatusInterface
     * @throws WorkflowException
     */
    public function getStatus($id, $model = null)
    {
        list($wId, $stId) = $this->parseStatusId($id, $model);
        $canonicalStId = $wId . self::SEPARATOR_STATUS_NAME . $stId;
        if (!isset($this->_s[$wId])) {
            $this->_s[$wId] = [];
        }
        if (!array_key_exists($canonicalStId, $this->_s[$wId])) {
            $statusModel = \cornernote\workflow\manager\models\Status::findOne([
                'workflow_id' => $wId,
                'id' => $stId
            ]);
            if ($statusModel == null) {
                throw new WorkflowException('No status found with id ' . $id);
            }
            $this->_s[$wId][$canonicalStId] = Yii::createObject([
                'class' => $this->getClassMapByType(self::TYPE_STATUS),
                'id' => $canonicalStId,
                'workflowId' => $statusModel->workflow_id,
                'label' => $statusModel->label ? $statusModel->label : Inflector::camel2words($stId, true),
                'source' => $this,
                'metadata' => ArrayHelper::map($statusModel->metadatas, 'key', 'value'),
            ]);
        }
        return $this->_s[$wId][$canonicalStId];
    }

    /**
     * @param string $workflowId
     * @return Status[]|StatusInterface[]
     */
    public function getAllStatuses($workflowId)
    {
        if (empty($this->_allStatusLoaded[$workflowId])) {
            $this->_s[$workflowId] = [];
            /** @var \cornernote\workflow\manager\models\Status[] $statusModels */
            $statusModels = \cornernote\workflow\manager\models\Status::find()
                ->where(['workflow_id' => $workflowId])
                //->andWhere(['NOT IN', 'id', array_keys($this->_s[$workflowId])]) // removed to fix sort order
                ->orderBy(['sort_order' => SORT_ASC])
                ->all();
            foreach ($statusModels as $statusModel) {
                $canonicalStId = $workflowId . self::SEPARATOR_STATUS_NAME . $statusModel->id;
                $this->_s[$workflowId][$canonicalStId] = Yii::createObject([
                    'class' => $this->getClassMapByType(self::TYPE_STATUS),
                    'id' => $canonicalStId,
                    'workflowId' => $workflowId,
                    'label' => $statusModel->label ? $statusModel->label : Inflector::camel2words($statusModel->id, true),
                    'source' => $this,
                    'metadata' => ArrayHelper::map($statusModel->metadatas, 'key', 'value'),
                ]);
            }
            $this->_allStatusLoaded[$workflowId] = true;
        }
        return $this->_s[$workflowId];
    }

    /**
     * @param mixed $statusId
     * @param null $model
     * @return Transition|TransitionInterface[]
     * @throws WorkflowException
     */
    public function getTransitions($statusId, $model = null)
    {
        list($wId, $stId) = $this->parseStatusId($statusId, $model);
        $statusId = $wId . self::SEPARATOR_STATUS_NAME . $stId;
        if (!isset($this->_t[$wId])) {
            $this->_t[$wId] = [];
        }
        if (!array_key_exists($statusId, $this->_t[$wId])) {
            $transitions = [];
            $transitionModels = \cornernote\workflow\manager\models\Transition::find()
                ->andWhere([
                    '{{%sw_transition}}.workflow_id' => $wId,
                    '{{%sw_transition}}.start_status_id' => $stId,
                ])
                ->leftJoin('{{%sw_status}}', '{{%sw_status}}.id = {{%sw_transition}}.end_status_id AND {{%sw_status}}.workflow_id = :workflow_id', [
                    ':workflow_id' => $wId,
                ])
                ->orderBy(['{{%sw_status}}.sort_order' => SORT_ASC])
                ->all();
            foreach ($transitionModels as $transition) {
                $endId = $wId . self::SEPARATOR_STATUS_NAME . $transition->end_status_id;
                $transitions[] = Yii::createObject([
                    'class' => $this->getClassMapByType(self::TYPE_TRANSITION),
                    'start' => $this->getStatus($statusId),
                    'end' => $this->getStatus($endId),
                    'source' => $this
                ]);
            }
            $this->_t[$wId][$statusId] = $transitions;
        }
        return $this->_t[$wId][$statusId];
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
        if (!array_key_exists($id, $this->_w)) {
            $workflow = null;
            $def = $this->getWorkflowDefinition($id);
            if ($def != null) {
                unset($def[self::KEY_NODES]);
                $def['id'] = $id;
                if (isset($def[Workflow::PARAM_INITIAL_STATUS_ID])) {
                    $ids = $this->parseStatusId($def[Workflow::PARAM_INITIAL_STATUS_ID], $id);
                    $def[Workflow::PARAM_INITIAL_STATUS_ID] = implode(self::SEPARATOR_STATUS_NAME, $ids);
                } else {
                    throw new WorkflowException('failed to load Workflow ' . $id . ' : missing initial status id');
                }
                $def['class'] = $this->getClassMapByType(self::TYPE_WORKFLOW);
                $def['source'] = $this;
                $workflow = Yii::createObject($def);
            }
            $this->_w[$id] = $workflow;
        }
        return $this->_w[$id];
    }

    /**
     * Loads definition for the workflow whose id is passed as argument.
     *
     * The workflow Id passed as argument is used to create the class name of the object
     * that holds the workflow definition.
     *
     * @param string $id
     * @return mixed
     * @throws WorkflowException the definition could not be loaded
     */
    public function getWorkflowDefinition($id)
    {
        if (!$this->isValidWorkflowId($id)) {
            throw new WorkflowException('Invalid workflow Id : ' . VarDumper::dumpAsString($id));
        }
        if (!isset($this->_workflowDef[$id])) {
            if ($this->getDefinitionCache() != null) {
                $cache = $this->getDefinitionCache();
                $key = $cache->buildKey('yii2-workflow-def-' . $id);
                if ($cache->exists($key)) {
                    $this->_workflowDef[$id] = $cache->get($key);
                } else {
                    $this->_workflowDef[$id] = $this->loadDefinition($id);
                    $cache->set($key, $this->_workflowDef[$id]);
                }
            } else {
                $this->_workflowDef[$id] = $this->loadDefinition($id);
            }
        }
        return $this->_workflowDef[$id];
    }

    /**
     * Loads the definition oa a workflow.
     *
     * @param string $id
     * @return \cornernote\workflow\manager\models\Workflow
     * @throws WorkflowException
     * @internal param IWorkflowSource $source
     */
    public function loadDefinition($id)
    {
        $workflowModel = \cornernote\workflow\manager\models\Workflow::findOne(['id' => $id]);
        if (!$workflowModel) {
            return null;
            //throw new WorkflowException('No workflow found with id ' . $id);
        }
        return [
            'class' => 'raoul2000\workflow\base\Workflow',
            'id' => $workflowModel->id,
            Workflow::PARAM_INITIAL_STATUS_ID => $workflowModel->id . self::SEPARATOR_STATUS_NAME . $workflowModel->initial_status_id,
            'source' => $this
        ];
    }

    /**
     * Return the workflow definition cache component used by this workflow source or NULL if no cache is used.
     * @return null|Cache
     * @throws InvalidConfigException
     */
    public function getDefinitionCache()
    {
        if (!isset($this->definitionCache)) {
            return null;
        }
        if (!isset($this->_dc)) {
            if (is_string($this->definitionCache)) {
                $this->_dc = Yii::$app->get($this->definitionCache);
            } elseif (is_array($this->definitionCache)) {
                $this->_dc = Yii::createObject($this->definitionCache);
            } elseif (is_object($this->definitionCache)) {
                $this->_dc = $this->definitionCache;
            } else {
                throw new InvalidConfigException('invalid "definitionCache" attribute : string or object expected');
            }
            if (!$this->_dc instanceof Cache) {
                throw new InvalidConfigException('the workflow definition cache must implement the yii\caching\Cache interface');
            }
        }
        return $this->_dc;
    }

    /**
     * Returns the class map array for this Workflow source instance.
     *
     * @return string[]
     */
    public function getClassMap()
    {
        return $this->_classMap;
    }

    /**
     * Returns the class name that implement the type passed as argument.
     * There are 3 built-in types that must have a class name :
     *
     * - self::TYPE_WORKFLOW
     * - self::TYPE_STATUS
     * - self::TYPE_TRANSITION
     *
     * The constructor ensure that if a class map is provided, it include class names for these 3 types. Failure to do so
     * will result in an exception being thrown by the constructor.
     *
     * @param string $type Type name
     * @return string | null the class name or NULL if no class name is found forthis type.
     */
    public function getClassMapByType($type)
    {
        return array_key_exists($type, $this->_classMap) ? $this->_classMap[$type] : null;
    }

    /**
     * @param string $val canonical id (e.g. myWorkflow/myStatus)
     * @param BaseActiveRecord|SimpleWorkflowBehavior $helper
     * @return array
     * @throws WorkflowException
     */
    public function parseStatusId($val, $helper = null)
    {
        if (empty($val) || !is_string($val)) {
            throw new WorkflowException('Not a valid status id : a non-empty string is expected  - status = ' . VarDumper::dumpAsString($val));
        }
        $tokens = array_map('trim', explode(self::SEPARATOR_STATUS_NAME, $val));
        $tokenCount = count($tokens);
        if ($tokenCount == 1) {
            $tokens[1] = $tokens[0];
            $tokens[0] = null;
            if (!empty($helper)) {
                if (is_string($helper)) {
                    $tokens[0] = $helper;
                } elseif ($helper instanceof BaseActiveRecord) {
                    $tokens[0] = $helper->hasWorkflowStatus()
                        ? $helper->getWorkflowStatus()->getWorkflowId()
                        : $helper->getDefaultWorkflowId();
                }
            }
            if ($tokens[0] === null) {
                throw new WorkflowException('Not a valid status id format: failed to get workflow id - status = ' . VarDumper::dumpAsString($val));
            }
        } elseif ($tokenCount != 2) {
            throw new WorkflowException('Not a valid status id format: ' . VarDumper::dumpAsString($val));
        }

        if (!$this->isValidWorkflowId($tokens[0])) {
            throw new WorkflowException('Not a valid status id : incorrect workflow id format in ' . VarDumper::dumpAsString($val));
        } elseif (!$this->isValidStatusLocalId($tokens[1])) {
            throw new WorkflowException('Not a valid status id : incorrect status local id format in ' . VarDumper::dumpAsString($val));
        }
        return $tokens;
    }

    /**
     * Checks if the string passed as argument can be used as a workflow ID.
     *
     * A workflow ID is a string that matches self::PATTERN_ID.
     *
     * @param string $val
     * @return boolean TRUE if the $val can be used as workflow id, FALSE otherwise
     */
    public function isValidWorkflowId($val)
    {
        return is_string($val) && preg_match(self::PATTERN_ID, $val) != 0;
    }

    /**
     * Checks if the string passed as argument can be used as a status local ID.
     *
     * @param string $val
     * @return boolean
     */
    public function isValidStatusLocalId($val)
    {
        return is_string($val) && preg_match(self::PATTERN_ID, $val) != 0;
    }

}
