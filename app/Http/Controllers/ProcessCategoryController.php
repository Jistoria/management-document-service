<?php

namespace App\Http\Controllers;

use App\Helpers\ApiIndexBuilder;
use App\Http\Requests\Process\FiltersProcessCategoryRequest;
use App\Http\Resources\ProcessCategoryResource;
use App\Services\ProcessCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

class ProcessCategoryController extends Controller
{

    protected $processCategoryService;

    public function __construct(ProcessCategoryService $processCategoryService)
    {
        $this->processCategoryService = $processCategoryService;
    }

    public function index(FiltersProcessCategoryRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $filter = $request->input('filter', []);

            return ApiIndexBuilder::build(
                $this->processCategoryService,
                ProcessCategoryResource::class,
                $request,
                $filter,
            );
        });
    }

    public function show($categoryId)
    {
        // Logic to retrieve a specific process category by ID
    }

    public function store(Request $request)
    {
        // Logic to create a new process category
    }

    public function update(Request $request, $categoryId)
    {
        // Logic to update an existing process category
    }

    public function destroy($categoryId)
    {
        // Logic to delete a process category
    }
}
