<?php 

namespace Kanvas\MagicImport\Libraries;

use Kanvas\MagicImport\Contracts\ColumnsInterface;
use Phalcon\DI\Injectable;
use ReflectionClass;
use Exception;

/**
 * Class Structure
 */
class Imports extends Injectable
{
    /**
     * @var Kanvas\MagicImport\Contracts\ColumnsInterface
     */
    public $structure;

    /**
     * Define if it is a test or is a real process
     * @var Boolean
     */
    public $commit;

    /**
     * namespace for the model
     * @var string
     */
    public $namespaceModel;

    /**
     * db connect
     * @var string
     */
    public $db;

    /**
     * @param Phalcon\Mvc\Model $model
     * @param array $fields
     */
    function __construct(ColumnsInterface $structure, bool $commit)
    {
        $this->db = $this->di->get('db');
        $this->structure = $structure;
        $this->commit = $commit;
        $this->namespaceModel = (new ReflectionClass($this->structure->model))->getNamespaceName();
    }

    /**
     * process data from array
     * @param array $data
     */
    public function processData(array $data)
    {
        $processData = $this->structureData($data);

        $this->db->begin();
        $return = [];
        foreach ($processData as $modelData) {
            try {
                $models = $this->save($modelData);
                $return[] = $models;
            } catch (Exception $e) {
                
            }
        }

        $this->finish();

        return $return;
    }

    /**
     * Save model data
     * @param array $modelData
     * @return array $models
     */
    public function save(array $modelData)
    {
        $return = [];
        foreach ($modelData as $model => $data) {
            $obj = new $model();
            $obj->assign($data);
            $obj->save();
            $return[] = $obj;
        }
        return $return;
    }

    /**
     * we define if it will be a preview or if we save in the db
     */
    private function finish() : void
    {
        if($this->commit){
            $this->db->commit();
            return;
        }
        $this->db->rollback();
    }

    /**
     * we organize the data
     * @param array $data
     * @return array
     */
    public function structureData(array $data) : array
    {
        $maps = $data['mapping']['map'];
        $fileValues = $data['fileValues'];
        $processData = [];

        foreach ($fileValues as $key => $data) {
            $processData[] = $this->getStructureData($data, $maps);
        }

        return $processData;
    }

    /**
     * Mapping data
     * @param array $raw
     * @param array $maps
     * @return array $processData
     */
    public function getStructureData($raw, $maps): array
    {
        $processData = [];

        foreach ($maps as $order => $value) {
            /**
             * 0 => Model
             * 1 => db tb name
             */
            $map = explode('.',$value);

            $processData["{$this->namespaceModel}\\{$map[0]}"][$map[1]] = $raw[$order];
        }

        return $processData;
    }
}