<?php

namespace App\Http\Controllers;

use App\Services\FacultyService;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

class FacultyController extends Controller
{
    public function __construct(
        protected FacultyService $facultyService
    )
    {}

    public function index(Request $request)
    {
        return catchSync(function () use ($request) {
            return $this->facultyService->getFaculties($request->all());
        }, 'faculties');
    }

    public function show($id)
    {
        return catchSync(function () use ($id) {
            return $this->facultyService->getFacultyById($id);
        }, 'faculty');
    }


    public function store(Request $request)
    {
        return catchSync(fn() => $this->facultyService->createFaculty($request->all()), 'faculty');
    }

    public function update(Request $request, $id)
    {
        return catchSync(function () use ($request, $id) {
            return $this->facultyService->updateFaculty($id, $request->all());
        }, 'faculty');
    }

    public function destroy($id)
    {
        return catchSync(function () use ($id) {
            return $this->facultyService->deleteFaculty($id);
        }, 'faculty');
    }
}
