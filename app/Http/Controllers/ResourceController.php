<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;

class ResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $resources = Resource::latest()->get();

            if ($resources->isEmpty()) {
                return response()->json([
                    "message" => "No resources found"
                ], 200);
            }
            return response()->json([
                "resource" => $resources
            ], 201);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreResourceRequest $request): JsonResponse
    {
        try {
            $attributes = $request->validated();
            $resource = Resource::create($attributes);
            return response()->json([
                "message" => "resources created successfully",
                "resource" => $resource
            ], 201);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Resource $resource): JsonResponse
    {
        try {
            return response()->json([
                "resource" => $resource
            ], 201);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 400);
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateResourceRequest $request, Resource $resource): JsonResponse
    {
        try {
            $attributes = $request->validated();
            $resource->update($attributes);
            return response()->json([
                "message" => "resource udpated successfully",
                "resource" => $resource
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resource $resource): JsonResponse
    {
        try {
            $resource->delete();
            return response()->json([
                "message" => "resource deleted successfully"
            ]);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 500);
        }
    }
}
