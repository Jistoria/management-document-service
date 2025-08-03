<?php

namespace App\Http\Controllers;

use App\Constants\HttpStatus;
use App\Helpers\ApiIndexBuilder;
use App\Http\Resources\ProcessResource;
use App\Services\ProcessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

class ProcessController extends Controller
{
    protected $processService;
    public function __construct(ProcessService $processService){
        $this->processService = $processService;
    }


    public function index(Request $request) : JsonResponse
    {
        return ApiIndexBuilder::build(
            $this->processService,
            ProcessResource::class,
            $request,
            $request->all()
        );
    }

    public function store(Request $request) : JsonResponse
    {
        return catchSync(function () use ($request) {
            $validated = $request->all();

            $process = $this->processService->create($validated);

            return new ProcessResource($process);
        }, status: HttpStatus::CREATED);
    }
}

