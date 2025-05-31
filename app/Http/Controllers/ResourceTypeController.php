<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResourceTypeRequest;
use App\Http\Requests\UpdateResourceTypeRequest;
use App\Models\ResourceType;
use Illuminate\Http\JsonResponse;

class ResourceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $resourceTypes = ResourceType::latest()->get();
            if ($resourceTypes->isEmpty()) {
                return response()->json([
                    "message" => "no resource type found"
                ], 200);
            }
            return response()->json([
                "resourceTypes" => $resourceTypes
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreResourceTypeRequest $request): JsonResponse
    {
        try {
            $attributes = $request->validated();
            $resource = ResourceType::create($attributes);
            return response()->json([
                "message" => "Resource Type Created Successfully"
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ResourceType $resourceType): JsonResponse
    {
        try {
            if ($resourceType->isEmpty()) {
                return response()->json([
                    "message" => "No resource type found for this id"
                ], 200);
            }
            return response()->json([
                "resourceType" => $resourceType
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage(),
            ], 500);
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateResourceTypeRequest $request, ResourceType $resourceType): JsonResponse
    {
        try {
            $attributes = $request->validated();
            $resourceType->update($attributes);
            return response()->json([
                "message" => "resourceType updated successfully"
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ResourceType $resourceType)
    {
        try {
            $resourceType->delete();
            return response()->json([
                "message" => "resourceType removed successfully"
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage(),
            ], 500);
        }
    }
}
