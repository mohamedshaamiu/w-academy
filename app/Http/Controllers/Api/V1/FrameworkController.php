<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FrameworkPillarResource;
use App\Http\Resources\Api\V1\FrameworkStrikeLevelResource;
use App\Models\FrameworkPillar;
use App\Models\FrameworkStrikeLevel;
use Illuminate\Http\JsonResponse;

class FrameworkController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'pillars' => FrameworkPillarResource::collection(FrameworkPillar::active()->get()),
            'strike_levels' => FrameworkStrikeLevelResource::collection(FrameworkStrikeLevel::active()->get()),
        ]);
    }
}
