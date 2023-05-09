<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalculatorRequest;
use App\Services\CalculatorService;
use Illuminate\Support\Facades\Http;

class CalculatorController extends Controller
{
    protected $service;

    /**
     * @param CalculatorService $service
     */
    public function __construct(CalculatorService $service)
    {
        $this->service = $service;
    }


    public function calculator(CalculatorRequest $request)
    {
        try {
            $data = $this->service->calculator($request);
            dd($data);
        }catch(\Exception $e){
            dd($e);
        }
    }
}
