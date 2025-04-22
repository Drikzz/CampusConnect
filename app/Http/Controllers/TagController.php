<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
  public function index()
  {
    return Tag::all();
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255|unique:tags',
    ]);

    return Tag::create([
      'name' => $validated['name'],
      'slug' => Str::slug($validated['name']),
    ]);
  }

  public function destroy(Tag $tag)
  {
    $tag->delete();
    return response()->json(['message' => 'Tag deleted successfully']);
  }
}