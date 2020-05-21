<?php 

namespace Kanvas\MagicImport\Libraries;

use Kanvas\MagicImport\Contracts\ColumnsInterface;
use Phalcon\DI\Injectable;

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
     * @param Phalcon\Mvc\Model $model
     * @param array $fields
     */
    function __construct(ColumnsInterface $structure, Boolean $commit)
    {
        $this->structure = $structure;
        $this->commit = $commit;
    }

    /**
     * process data from array
     * @param array $data
     */
    public function processData(array $data)
    {
        
    }
}