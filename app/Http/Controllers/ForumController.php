<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Post;

class ForumController extends Controller
{
        public function __construct()
    {
        $this->middleware('auth')->only(['storePost']);
    }
        // 1. Lista categorie
    public function index()
    {
        $tags = Tag::all();
        return view('forum.tags', compact('tags'));
    }

        // 2. Mostra i post di una categoria
    public function show(Tag $tag)
    {
        
        $posts = Post::where('tag_id', $tag->id)
        ->with('user')
        ->latest()
        ->paginate(10);
        
        return view('forum.show', compact('tag', 'posts'));
    }

        // 3. Salva nuovo post in categoria
    public function storePost(Request $request, Tag $tag)
    {
        $request->validate([
            'titolo' => 'required|string|max:255',
            'contenuto' => 'required|string|max:1000',
        ]);

        Post::create([
            'user_id' => auth()->id(),
            'titolo' => $request->titolo,
            'contenuto' => $request->contenuto,
            'tag_id' => $tag->id,
        ]);

        return redirect()->route('forum.show', $tag->id)->with('success', 'Post pubblicato!');
    }
    public function destroyPost(Post $post)
    {
        $user = auth()->user();

        if ($user->id === $post->user_id || $user->isAdmin()) {
            $post->delete();
            return back()->with('success', 'Post eliminato.');
        }

        return back()->with('error', 'Non autorizzato.');
    }


}
