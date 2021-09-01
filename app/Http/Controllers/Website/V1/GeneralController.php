<?php

namespace App\Http\Controllers\Website\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function changeTokenPassword()
    {
        $this->request->all();
    }

}
