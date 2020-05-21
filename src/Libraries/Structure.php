<?php 

namespace Kanvas\MagicImport\Libraries;

use Phalcon\DI\Injectable;
use Phalcon\Mvc\Model;
use Kanvas\MagicImport\Contracts\ColumnsInterface;

/**
 * Class Structure
 */
class Structure extends Injectable implements ColumnsInterface 
{
    /**
     * @var Phalcon\Mvc\Model
     */
    public $model;

    /**
     * @var array
     */
    public $fields;

    /**
     * @var string
     */
    public $class_base;

    /**
     * @param Phalcon\Mvc\Model $model
     * @param array $fields
     */
    function __construct(Model $model, $fields = null)
    {
        $this->model = $model; 
        $this->fields = $fields; 
        $this->class_base = get_class($model); 
    }

    /**
     * Get structure for import
     * @return array $fields
     */
    public function getStructure() : array
    {
        $fields = $this->fields;

        /**
         * If the user does not send data, we take it from the model
         */
        if (is_null($fields)) {
            $fields = $this->getFieldsByModel();
        }
        return $fields;
    }

    /**
     * Get fields by models
     * @return array $raw
     */
    public function getFieldsByModel() : array
    {
        $raw = [];
        /**
         * Get namespace and class name
         */
        $class = explode("\\",$this->class_base);

        /**
         * Get Structure for the main class
         */
        $raw[end($class)] = $this->setStructure($this->model);

        /**
         * Get relations from the models
         */
        foreach ($this->modelsManager->getRelations($this->class_base) as $relations) {
            /**
             * Name of relationship
             * @var string
             */
            $relationshipName = $relations->getReferencedModel();

            $class = explode("\\",$relationshipName);
            /**
             * Get Structure for the relationship class
             */
            $raw[end($class)] = $this->setStructure(new $relationshipName);
        }

        return $raw;
    }

    /**
     * Format models data to arrays
     * @param Phalcon\Mvc\Model $model
     * @return array $raw
     */
    public function setStructure(Model $model): array
    {
        $raw = [];
        
        foreach ($model->toArray() as $tbname => $value) {
            $raw['columns'][] = [
                "field" => $tbname,
                "type" => gettype($tbname),
                "validation" => false,
                "label" => ucfirst(str_replace('_',' ', $tbname))
            ];
        }

        return $raw;
    }
}