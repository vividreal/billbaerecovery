<?php

namespace App\Http\Controllers;
use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    protected $title        = 'Category';
    protected $viewPath     = 'category';
    protected $route        = 'categories';
    protected $link         = 'categories';
    protected $entity       = 'categories';
    protected $timezone     = '';
    protected $time_format  = '';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        if ($request->ajax()) {
            // Get all categories or modify query as needed
            $categories = Category::query();

            // Create DataTable response
            return DataTables::of($categories)
                ->addIndexColumn() // Automatically adds DT_RowIndex for row numbering
                ->addColumn('action', function ($row) {
                    // Return HTML for edit/delete buttons
                    return view('categories.partials.actions', compact('row'))->render();
                })
                ->rawColumns(['action']) // Mark the action column as raw HTML
                ->make(true);
        }
        return view('categories.index', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        return view('categories.create',compact('page'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        Category::create($request->only(['name', 'description']));

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $category = Category::findOrFail($id);

        return view('categories.edit', compact('category','page'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        
        $request->validate([
            'name' => 'required|unique:categories,name,' . $category->id . '|max:255',
            'description' => 'nullable|string|max:1000'
        ]);
      
        $category->update($request->only(['name', 'description']));
    
        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
