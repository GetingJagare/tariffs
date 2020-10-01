<?php
/**
 * Created by PhpStorm.
 * User: wanderer
 * Date: 01.10.20
 * Time: 14:58
 */

namespace App\Services;


use Illuminate\Http\Request;

class TariffsImporter
{
    /** @var Request  */
    private $request;

    /**
     * TariffsImporter constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function doImport()
    {



    }
}
