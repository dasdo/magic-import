<?php namespace Kanvas\MagicImport\Contracts;

use Kanvas\MagicImport\Libraries\Structure;

trait ImportsTrait
{
    /**
     * @return Phalcon\Http\Response
     */
    public function structure()
    {
        $base = new Structure($this->model);
        $array = $base->getStructure();

        return $this->response($array);
    }
}