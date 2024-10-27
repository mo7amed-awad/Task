<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $tags = Tag::all();
        if ($tags->isNotEmpty()) {
            return ApiResponse::sendResponse(200, "Tags Retrieved Successfully", TagResource::collection($tags));
        }

        // Return 404 if no tags are found
        return ApiResponse::sendResponse(404, 'No Tags Found', []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:tags'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, "Validation Errors", $validator->messages()->all());
        }

        $tag = Tag::create([
            'name' => $request->name,
        ]);

        return ApiResponse::sendResponse(201, "Tag Created Successfully", new TagResource($tag));
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $tag = Tag::find($id);
        if (!$tag) {
            return ApiResponse::sendResponse(404, "Tag Not Found", []);
        }

        return ApiResponse::sendResponse(200, "Tag Retrieved Successfully", new TagResource($tag));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        // Find the tag by ID or fail with a 404 error
        $tag = Tag::findOrFail($id);
        
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:tags,name,' . $tag->id, // Exclude current tag ID from uniqueness check
            ],
        ]);
    
        // Check for validation errors
        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, "Validation Errors", $validator->messages()->all());
        }
    
        // Update the tag
        $tag->update([
            'name' => $request->name,
        ]);
    
        // Return the response indicating successful update
        return ApiResponse::sendResponse(200, "Tag Updated Successfully", new TagResource($tag));
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        // Find the tag by ID or fail with a 404 error
        $tag = Tag::findOrFail($id);
        
        // Delete the tag
        $tag->delete();

        return ApiResponse::sendResponse(200, "Tag Deleted Successfully", []);
    }
}
