<?php
namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class PostController extends Controller
{
    // Display all posts of the authenticated user, pinned posts first
    public function index()
    {
        $posts = Post::where('user_id', Auth::id())
            ->orderBy('pinned', 'desc')
            ->get();

        if (!$posts) {
            return ApiResponse::sendResponse(404, "Posts Not Found", []);
        }

        return ApiResponse::sendResponse(200, "Posts Retrieved Successfully", PostResource::collection($posts));
    }

    // Store a new post
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'cover_image' => ['required', 'image'], // Allowing string (URL)
            'pinned' => 'required',
            'tags' => 'array', // optional
            'tags.*' => 'exists:tags,id', // validate tag IDs

        ]);
        $data = $request->except('cover_image');
        $data['cover_image'] = $this->uploadImage($request);

        // Check for validation errors
        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, "Validation Errors", $validator->messages()->all());
        }
        $data['user_id']=auth()->id();
        $post = Post::create($data);
        if ($request->tags) {
            $post->tags()->attach($request->tags);
        }

        return ApiResponse::sendResponse(201, "Post Created Successfully", new PostResource($post));
    }
    
    // Show a single post
    public function show($id)
    {
        $post = Post::where('user_id', Auth::id())->findOrFail($id);
        if (!$post) {
            return ApiResponse::sendResponse(404, "Post Not Found", []);
        }

        return ApiResponse::sendResponse(200, "Post Retrieved Successfully", new PostResource($post)); 
    }
    
    // Update a post
    public function update(Request $request, $id)
    {
        $post = Post::where('user_id', Auth::id())->findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'cover_image' => 'nullable|image',
            'pinned' => 'required|boolean',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);
        
        if ($validator->fails()) {
            return ApiResponse::sendResponse( 422, 'Validation Errors',$validator->messages());
        }
        
        $old_image = $request->cover_image;
        $data = $request->except('cover_image');
        $new_image = $this->uploadImage($request);
        if($new_image)
        {
            $data['cover_image'] = $new_image;
        }
        if($old_image && $new_image){
            Storage::disk('public')->delete($old_image);
        }

        $updated = $post->update($data);

        if ($request->tags) {
            $post->tags()->sync($request->tags);
        }

        if ($updated) {
            return ApiResponse::sendResponse(201, "Post Updated Successfully", new PostResource($post));
        } else {
            return ApiResponse::sendResponse(404, "Error in update Post", []);
        }        
        
        
    }

    // Soft delete a post
    public function destroy($id)
    {
        $post = Post::where('user_id', Auth::id())->findOrFail($id);
    
        if ($post->delete()) {
            return ApiResponse::sendResponse(200, 'Post deleted successfully.', []);
        } else {
            return ApiResponse::sendResponse(500, 'Error in deleting post.', []);
        }
    }

    // View soft deleted posts
    public function trashed()
    {
        $posts = Post::onlyTrashed()->where('user_id', Auth::id())->get();
        if (!$posts) {
            return ApiResponse::sendResponse(404, "There isn't deleted posts", []);
        }

        return ApiResponse::sendResponse(200, "Deleted Post Retrieved Successfully",PostResource::collection($posts)); 
    }

    // Restore a soft deleted post
    public function restore($id)
    {
        $post = Post::onlyTrashed()->where('user_id', Auth::id())->findOrFail($id);
        $post->restore();
        return new PostResource($post);
    }

    protected function uploadimage(Request $request)
    {

        if (!$request->hasFile('cover_image')) {
            return;
        }
        $file = $request->file('cover_image'); //uploaded file object
        $path = $file->store('uploads', [
            'disk' => 'public',
        ]);
        return $path;

    }
}
