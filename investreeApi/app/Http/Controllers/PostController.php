<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\BaseController;
use App\Http\Resources\PostResource;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Validator;

class PostController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index() : JsonResponse
    {
        $post = Post::paginate(5);

        return $this->sendResponse(PostResource::collection($post), 'Post retrieved succesfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user_id = Auth::id();

        $post = new Post();
        $post->user_id = $user_id;
        $post->title = $request->input('title');
        $post->content = $request->input('content');

        $image = $request->file('image');
        if ($image) {
            $fileName = time().'.'.$image->getClientOriginalExtension();
            $path = public_path('images/post_image');
            $image->move($path, $fileName);
            $post->image = $fileName;
        }


        if ($request->has('category_name')) {
            $category = new Category();
            $category->name = $request->input('category_name');
            $category->user_id = $user_id;
            $category->save();
    
            $post->categories_id = $category->id;
            $post->save();
        }

        return $this->sendResponse(new PostResource($post), 'Post succesfully created');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::find($id);

        if(is_null($post))
        {
            return $this->sendError('post not found.');
        }

        return $this->sendResponse(new PostResource($post),'Post retrieved succesfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user_id = Auth::id();
        $post = Post::findOrFail($id);
    
        
        if ($post->user_id !== $user_id) {
            return $this->sendError('Unauthorized', ['You are not authorized to update this post.']);
        }
    
        
        $post->title = $request->input('title');
        $post->content = $request->input('content');
    
        $image = $request->file('image');
        if ($image) {
            $fileName = time().'.'.$image->getClientOriginalExtension();
            $path = public_path('images/post_image');
            $image->move($path, $fileName);
            $post->image = $fileName;
        }
    
        
        if ($request->has('category_name')) {
            $category = Category::firstOrCreate([
                'user_id' => $user_id,
                'name' => $request->input('category_name'),
            ]);
    
            $post->categories_id = $category->id;
        }
    
        
        $post->save();
    
        return $this->sendResponse(new PostResource($post), 'Post successfully updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return $this->sendResponse([], 'Post successfully deleted');

    }
}
