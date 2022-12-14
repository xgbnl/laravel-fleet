<?php

namespace Xgbnl\Fleet\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Xgbnl\Fleet\Traits\BusinessGenerator;
use Xgbnl\Fleet\Traits\CallMethodCollection;

abstract class AbstractController extends Controller
{
    use BusinessGenerator,CallMethodCollection;

    protected ?Request $request = null;

    public function callAction($method, $parameters)
    {
        $injected = false;

        foreach ($parameters as $p) {
            if ($p instanceof Request) {
                $this->request = $p;
                $injected      = true;
            }
        }

        if (!$injected) {
            $this->request = \request();
        }

        return parent::callAction($method, $parameters); // TODO: Change the autogenerated stub
    }
}
