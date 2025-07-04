<?php

namespace App\Services;

use App\Models\Faculty;

class FacultyService
{
    public function __construct(
        protected Faculty $faculty
    )
    {}

    public function getFaculties(array $request)
    {
        $query = $this->faculty->query();

        $query->when($request['name'] ?? null, function ($q, $request) {
            $q->where('name', 'like', '%' . $request['name'] . '%');
        });

        return $query->get();
    }

    public function getFacultyById(string $id)
    {
        return $this->faculty->findOrFail($id);
    }

    public function createFaculty(array $data)
    {
        return $this->faculty->create($data);
    }

    public function updateFaculty(string $id, array $data)
    {
        $faculty = $this->faculty->findOrFail($id);
        $faculty->update($data);
        return $faculty;
    }

    public function deleteFaculty($id)
    {
        $faculty = $this->faculty->findOrFail($id);
        $faculty->delete();
        return response()->json(['message' => 'Faculty deleted successfully'], 200);
    }

}
