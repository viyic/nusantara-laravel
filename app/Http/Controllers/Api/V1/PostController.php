<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = new Post;

        return PostResource::collection($posts->paginate());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'text' => ['required', 'string', 'max:255'],
            ]);
            $post = Post::create([
                'user_id' => auth()->id(),
                'text' => $request->text,
            ]);

            return response()->json(["message" => "Berhasil membuat postingan", "post" => $post]);
        } catch (\Exception $e) {
            return response()->json(["message" => "Gagal membuat postingan"], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
        return response()->json($post);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Post $post)
    {
        try {
            $request->validate([
                'text' => ['required', 'string', 'max:255'],
            ]);
            $post->text = $request->text;
            $post->save();

            return response()->json(["message" => "Berhasil membuat postingan"]);
        } catch (\Exception $e) {
            return response()->json(["message" => "Gagal membuat postingan"], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }
}
