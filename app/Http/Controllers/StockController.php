<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Product;
use App\Models\Stock;



class StockController extends Controller
{
    protected $title        = 'Stocks';
    protected $viewPath     = 'stocks';
    protected $route        = 'stocks';
    protected $link         = 'stocks';
    protected $entity       = 'stocks';
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
        $page->title            =  $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        if ($request->ajax()) {
            // Fetch products with their categories
            $stocks = Stock::with(['product', 'product.category']);

            return DataTables::of($stocks)
            ->addIndexColumn()
            ->addColumn('image', function ($stock) {
                // Display product image if available
                return '<img src="' . url('storage/' . $stock->product->image) . '" alt="Stock Image" style="width: 50px; height: auto;">';
            })
            ->addColumn('name', function ($stock) {
                return $stock->product->name;
            })
            ->addColumn('category_name', function ($stock) {
                return $stock->product->category->name ?? 'N/A';
            })
            ->addColumn('product_type', function ($stock) {
                return $stock->product->product_type ?? 'N/A';
            })
            ->addColumn('product_code', function ($stock) {
                return $stock->product->product_code?? 'N/A';
            })
            ->addColumn('price', function ($stock) {
                return $stock->product->price?? 'N/A';
            })
            ->addColumn('total_stock', function ($stock) {
                return $stock->quantity?? 'N/A';
            })
            ->addColumn('balance_stock', function ($stock) {
                return $stock->balance_quantity?? 'N/A';
            })
            ->addColumn('action', function ($row) {
                return view('stocks.partials.actions', compact('row'))->render();
            })
            ->rawColumns(['image', 'action']) 
            ->make(true);

        }
        return view('stocks.index', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Product $product)
    {
        $page                   = collect();
        $variants               = collect();
        $page->title            =  $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $products = Product::where('shop_id',SHOP_ID)->get();
        return view('stocks.create', compact('products','page'));
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
            'quantity' => 'required|integer',
            'product_id'=>'required',
        ]);
        $shop_id=SHOP_ID;
        $stock = Stock::firstOrNew([
            'product_id' => $request->product_id,
            'shop_id' => $shop_id
        ]);
        
        // If the stock exists, increment the quantity and balance_quantity
        if ($stock->exists) {
            $stock->quantity += $request->quantity;
            $stock->balance_quantity += $request->quantity;
        } else {
            // If it's a new stock entry, set the quantity and balance_quantity
            $stock->quantity = $request->quantity;
            $stock->balance_quantity = $request->quantity;
        }
        
        // Save the stock record
        $stock->save(); 
        return redirect()->route('stocks.index')->with('success', 'Stock updated successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $products = Product::where('shop_id',SHOP_ID)->get();
        $stock = Stock::find($id);
        return view('stocks.edit',compact('products','page','stock'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer',
            'product_id'=>'required',
        ]);
        
        $stock = Stock::find($id);    
        if ($stock && $stock->product_id == $request->product_id) { 
            $stock->quantity = $request->quantity;
            $stock->balance_quantity = $request->quantity;
        }

        $stock->save();
        return redirect()->route('stocks.index')->with('success', 'Stock updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stock $stock)
    {
        $stock->delete();
        return redirect()->route('stocks.index')->with('success','Stock deleted successfully');
    }
}
