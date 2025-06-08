<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return inertia('categories/Index');
    }

    public function list()
    {
        $categories = Category::select('id', 'name', 'slug', 'description')->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|unique:categories',
            'description' => 'nullable',
        ]);

        Category::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name'        => "required|unique:categories,name,$id",
            'description' => 'nullable',
        ]);

        $category->name        = $request->name;
        $category->slug        = Str::slug($request->name);
        $category->description = $request->description;
        $category->save();

        return redirect()->back();
    }

    public function destroy($id)
    {
        Category::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
