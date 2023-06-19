<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'fechaInicio' => 'required|date',
            'fechaFinal' => 'required|date',
        ]);

        $imageName = time().'.'.$request->image->extension();  
        $request->image->move(public_path('images'), $imageName);
   
        $post = new Post();
        $post->message = $request->message;
        $post->image = '/images/'.$imageName;
        $post->fechaInicio = $request->fechaInicio;
        $post->fechaFinal = $request->fechaFinal;
        $post->save();

        return response()->json(['message' => 'Post created successfully.']);
    }

    public function index()
    {
        $posts = Post::all();
        return response()->json($posts);
    }

    public function deletePost($idPost)
    {
        if(Post::where('id',$idPost)->exists()){
            $post = Post::where('id',$idPost)->first();
            $post->delete();
        }else{
            return response([
                'status' => '0',
                'msg' => 'Error',
            ],404);
        }
    }

    public function listAllPosts()
    {
        
        $posts = Post::get();     
        
        $posts->each(function ($reserva){
            $reserva->makeHidden('image');
            $reserva->makeHidden('created_at');
            $reserva->makeHidden('updated_at');
        });

        return $posts;       
    }
}
