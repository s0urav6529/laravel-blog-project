<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\SubCategory;
use Illuminate\Support\Str;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    final public function index()
    {

        $query = Post::with('category', 'sub_category', 'user', 'tag')->latest();

        if (Auth::user()->role == User::USER) {
            $query->where('user_id', Auth::id());
        }
        $post_data = $query->paginate(10);

        return view('backend.modules.post.index', compact('post_data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $category_data = Category::where('status', 1)->pluck('name', 'id');
        $tag_data = Tag::where('status', 1)->select('name', 'id')->get();
        return view('backend.modules.post.create', compact('category_data', 'tag_data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostStoreRequest $request)
    {

        $post_data = $request->except(['tag_ids', 'photo']);
        $post_data['slug'] = Str::slug($request->input('slug'));
        $post_data['user_id'] = Auth::user()->id;
        $post_data['is_approved'] = 1;

        if ($request->hasFile('photo')) {

            $file = $request->file('photo');
            $name = Str::slug($request->input('slug'));
            $height = 400;
            $width = 1000;

            $thumb_height = 150;
            $thumb_width = 300;

            $path = 'images/post/original/';
            $thumb_path = 'images/post/thumbnail/';

            $post_data['photo'] = PhotoUploadController::imageUpload($name, $height, $width, $path, $file);
            PhotoUploadController::imageUpload($name, $thumb_height, $thumb_width, $thumb_path, $file);
        }

        $post = Post::create($post_data);

        $post->tag()->attach($request->input('tag_ids'));

        session()->flash('msg', 'Post created successfully !');
        session()->flash('notification_color', 'success');

        return redirect()->route('post.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {

        if (Auth::user()->role == User::USER && $post->user_id != Auth::id()) {
            return abort(403);
        }

        $post->load(['category', 'sub_category', 'user', 'tag']);
        return view('backend.modules.post.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        $category_data = Category::where('status', 1)->pluck('name', 'id');
        $tag_data = Tag::where('status', 1)->select('name', 'id')->get();
        $selected_tags = DB::table('post_tag')->where('post_id', $post->id)->pluck('tag_id')->toArray();

        /* $post->load('tag');
        $selected_tags = $post->tag->pluck('id')->toArray(); */

        return view('backend.modules.post.edit', compact('post', 'category_data', 'tag_data', 'selected_tags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostUpdateRequest $request, Post $post)
    {
        $post_data = $request->except(['tag_ids', 'photo']);
        $post_data['slug'] = Str::slug($request->input('slug'));
        $post_data['user_id'] = Auth::user()->id;
        $post_data['is_approved'] = 1;

        if ($request->hasFile('photo')) {

            $file = $request->file('photo');
            $name = Str::slug($request->input('slug'));
            $height = 400;
            $width = 1000;

            $thumb_height = 150;
            $thumb_width = 300;

            $path = 'images/post/original/';
            $thumb_path = 'images/post/thumbnail/';

            PhotoUploadController::imageUnlink($path, $post->photo);
            PhotoUploadController::imageUnlink($thumb_path, $post->photo);

            $post_data['photo'] = PhotoUploadController::imageUpload($name, $height, $width, $path, $file);
            PhotoUploadController::imageUpload($name, $thumb_height, $thumb_width, $thumb_path, $file);
        }

        $post->update($post_data);
        $post->tag()->sync($request->input('tag_ids'));

        session()->flash('msg', 'Post updated successfully !');
        session()->flash('notification_color', 'success');

        return redirect()->route('post.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $path = 'images/post/original/';
        $thumb_path = 'images/post/thumbnail/';

        PhotoUploadController::imageUnlink($path, $post->photo);
        PhotoUploadController::imageUnlink($thumb_path, $post->photo);
        $post->delete();

        session()->flash('msg', 'Post deleted successfully !');
        session()->flash('notification_color', 'success');

        return redirect()->route('post.index');
    }
}
