<?php namespace Kanvas\MagicImport\Libraries\Columns;

class Base extends Columns 
{
    public $model;
    public $fields;

    function __construct($model, $fields = null)
    {
        $this->model = $model; 
        $this->fields = $fields; 
    }

    public function getStructure()
    {
        $fields = $this->fields;

        if (is_null($fields)) {
            $fields = $this->getFieldsByModel();
        }
    }

    public function getFieldsByModel()
    {
        $class = get_class($this->model);
        $raw = [];
        
        foreach ($this->model->toArray() as $tbname => $value) {
            $raw[$class]['columns'] = [
                "field" => $tbname,
                "type" => gettype($tbname),
                "validation" => false,
                "label" => ucfirst(str_replace('_',' ', $tbname))
            ];
        }
    }
}