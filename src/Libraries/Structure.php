<?php 

namespace Kanvas\MagicImport\Libraries;

use Phalcon\DI\Injectable;
use Phalcon\Mvc\Model;
use Kanvas\MagicImport\Contracts\ColumnsInterface;
use Phalcon\Mvc\Model\Relation;

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
     * @var array
     */
    public $relationship;

    /**
     * @var string
     */
    public $class_base;

    /**
     * @param Phalcon\Mvc\Model $model
     * @param array $fields
     * @param array $relationship
     */
    function __construct(Model $model, $fields = [], $relationship = [])
    {
        $this->model = $model; 
        $this->fields = $fields;
        $this->relationship = $relationship;
        $this->class_base = get_class($model); 
    }

    /**
     * Get structure for import
     * @return array $fields
     */
    public function getStructure() : array
    {
        $fields = $this->getFieldsByModel();
        return array_merge($fields, $this->fields);
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

            $relationshipClass = explode("\\",$relationshipName);

            /**
             * Get Structure for the relationship class
             */
            $raw[end($relationshipClass)] = $this->setStructure(new $relationshipName);

            $relationships[$relationshipName] = $this->getRelationshipsKeys($relations);
        }

        /**
         * Set relationship
         */
        $raw[end($class)]['relationships'] = array_merge($relationships, $this->relationship);

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

    /**
     * get primary keys from Relationships
     * @param $relationships
     */
    public function getRelationshipsKeys(Relation $relationships) : array
    {
        $keys = [];

        /**
         * Name of relationship
         * @var string
         */
        $keys['relationshipName'] = $relationships->getReferencedModel();

        /**
         * Relationships Types
         * 1 => hasOne
         * 2 => hasMany
         * @var int
         */
        $keys['getType'] = $relationships->getType();

        /**
         * Primary key from the models
         * @var string
         */
        $keys['primaryKey'] = $relationships->getFields();

        /**
         * relationships
         */
        $keys['relationshipsKey'] = $relationships->getReferencedFields();

        /**
         * Variable that defines if it is unique
         */
        //$model = new $keys['relationshipName'];
        //$pk = $model->getModelsMetaData()->getPrimaryKeyAttributes($model);
        //$keys['unique'] = $pk;

        return $keys;
    }
}