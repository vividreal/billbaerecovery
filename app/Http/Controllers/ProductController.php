<?php
    
namespace App\Http\Controllers;
    
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
    
class ProductController extends Controller
{ 
    protected $title        = 'Products';
    protected $viewPath     = 'products';
    protected $route        = 'products';
    protected $link         = 'products';
    protected $entity       = 'products';
    protected $timezone     = '';
    protected $time_format  = '';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page                   = collect();
        $variants               = collect();
        $page->title            =  $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        if ($request->ajax()) {
            // Fetch products with their categories
            $products = Product::with('category');
            return DataTables::of($products)
                ->addIndexColumn() 
                ->addColumn('image', function($product) {
                    return '<img src="' . url('storage/' . $product->image) . '" alt="Product Image" style="width: 50px; height: auto;">';
                })
                ->addColumn('bill_image', function($product) {
                    return '<img src="' . url('storage/' . $product->bill_image) . '" alt="Bill Image" style="width: 50px; height: auto;">';
                })
                ->addColumn('action', function ($row) {
                    return view('products.partials.actions', compact('row'))->render();
                })
                ->rawColumns(['image', 'bill_image', 'action']) // Mark the action column as raw HTML
                ->make(true);
        }
       
        return view('products.index', compact('page'));
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
        $page->title            =  $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $categories = Category::get();
        return view('products.create', compact('categories','page'));
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
            'name' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Validate image file

        ]);
        $product_code = 'PRD-' . strtoupper(Str::random(5)) . '-' . time();
        $shop_id=SHOP_ID;
       
        $productData = array_merge($request->all(), [
            'product_code' => $product_code,
            'shop_id' => $shop_id
        ]);
       
        // Handle the image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $productData['image'] = $imagePath; // Store path to save in database
        }   
        if ($request->hasFile('bill_image')) {
            $bill_imagePath = $request->file('bill_image')->store('products', 'public');
            $productData['bill_image'] = $bill_imagePath; // Store path to save in database
        }       
        Product::create($productData);
        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        $product->load('stocks'); // Load stock transactions
        return view('products.show', compact('product'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $product = Product::where('shop_id',SHOP_ID)->findOrFail($id);
        $categories= Category::get();
        return view('products.edit',compact('product','page','categories'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
         request()->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        $productData =$request->all();
         if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $productData['image'] = $imagePath; // Store path to save in database
        }   
        if ($request->hasFile('bill_image')) {
            $bill_imagePath = $request->file('bill_image')->store('products', 'public');
            $productData['bill_image'] = $bill_imagePath; // Store path to save in database
        }
        $product->update($productData);
        return redirect()->route('products.index')->with('success','Product updated successfully');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success','Product deleted successfully');
    }
}