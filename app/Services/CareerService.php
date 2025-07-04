<?php

namespace App\Services;

use App\Models\Career;

class CareerService
{
    public function __construct(
        protected Career $career
    )
    {}

    public function getCareers(array $request)
    {
        $query = $this->career->query();

        $query->when($request['name'] ?? null, function ($q, $request) {
            $q->where('name', 'like', '%' . $request['name'] . '%');
        });

        return $query->get();
    }

    public function getCareerById(string $id)
    {
        return $this->career->findOrFail($id);
    }

    public function createCareer(array $data)
    {
        return $this->career->create($data);
    }

    public function updateCareer(string $id, array $data)
    {
        $career = $this->career->findOrFail($id);
        $career->update($data);
        return $career;
    }

    public function deleteCareer($id)
    {
        $career = $this->career->findOrFail($id);
        $career->delete();
        return response()->json(['message' => 'Career deleted successfully'], 200);
    }
}
