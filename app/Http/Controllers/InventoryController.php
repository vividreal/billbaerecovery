<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Stock;
use App\Models\Inventory;
use App\Models\Service;
use App\Models\Package;
use App\Models\StaffProfile;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InventoryController extends Controller
{
    protected $title        = 'Inventories';
    protected $viewPath     = 'inventories';
    protected $route        = 'inventories';
    protected $link         = 'inventories';
    protected $entity       = 'inventories';
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
            $inventories = Inventory::with('product', 'staffProfile', 'service', 'package','product.stock');
            return DataTables::of($inventories)
            ->addIndexColumn()
            ->addColumn('image', function($inventory) {
                return '<img src="' . url('storage/' . $inventory->product->image) . '" alt="Product Image" style="width: 50px; height: auto;">';
            })  
            ->addColumn('name', function ($inventory) {
                return $inventory->product->name;
            })
            ->addColumn('category_name', function ($inventory) {
                return $inventory->product->category->name ?? 'N/A';
            })
            ->addColumn('product_type', function ($inventory) {
                return $inventory->product->product_type ?? 'N/A';
            })
            ->addColumn('product_code', function ($inventory) {
                return $inventory->product->product_code?? 'N/A';
            })
          
            ->addColumn('total_stock', function ($inventory) {
                return $inventory->quantity?? 'N/A';
            })
            ->addColumn('takes_count', function ($inventory) {
                return $inventory->taking_quantity?? 'N/A';
            })
            ->addColumn('balance_stock', function ($inventory) {
                $balance_quantity=$inventory->quantity-$inventory->taking_quantity;
                return $balance_quantity> 0 ? $balance_quantity :0;
            })         
            ->addColumn('action', function ($row) {
                return view('inventories.partials.actions', compact('row'))->render();
            })
            ->rawColumns(['image','action']) 
            ->make(true);
        }
       
        return view('inventories.index', compact('page'));
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
        $products = Product::where('shop_id',SHOP_ID)->get();
        $services = Service::where('shop_id',SHOP_ID)->get();
        $packages = Package::where('shop_id',SHOP_ID)->get();
        $staffs   = StaffProfile::all();
        return view('inventories.create', compact('products','page','packages','services','staffs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction(); 
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'taking_quantity' => [
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) use ($request) {
                        // Check if the taking_quantity is not greater than the available stock
                        $inventory = Stock::where('product_id', $request->product_id)->first();
                        if ($inventory && $value > $inventory->balance_quantity) {
                            $fail('The taking quantity cannot exceed the available stock.');
                        }
                    },
                ],
                // Ensure either service_id or package_id is required, but not both
                'service_id' => [
                    'nullable',
                    'exists:services,id',
                    function ($attribute, $value, $fail) use ($request) {
                        if (!$value && !$request->package_id) {
                            $fail('Either Service or Package is required.');
                        }
                    }
                ],
                'package_id' => [
                    'nullable',
                    'exists:packages,id',
                    function ($attribute, $value, $fail) use ($request) {
                        if (!$value && !$request->service_id) {
                            $fail('Either Service or Package is required.');
                        }
                    }
                ],
            ]);     
            $stock=Stock::where('shop_id',SHOP_ID)->where('product_id',$request->product_id)->first();
            $productData = array_merge($request->all(), [        
                'shop_id' => SHOP_ID,
                'quantity'=>$stock->balance_quantity,
            ]);       
            
            $stock->taking_quantity     =$request->taking_quantity;
            $stock->balance_quantity   -=$request->taking_quantity;
            $stock->save();
           
            Inventory::create($productData);

            DB::commit();
            return redirect()->route('inventories.index')->with('success', 'Inventory created successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction on failure
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
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
        $page->title            =  $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $products = Product::where('shop_id',SHOP_ID)->get();
        $services = Service::where('shop_id',SHOP_ID)->get();
        $packages = Package::where('shop_id',SHOP_ID)->get();
        $staffs   = StaffProfile::all();
        $inventory= Inventory::find($id);
        return view('inventories.edit', compact('products','page','packages','services','staffs','inventory'));
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
        try {
            DB::beginTransaction(); 
            $currentInventory = Inventory::findOrFail($id); // Assuming the ID is passed in the request

            $request->validate([
                'product_id' => 'required|exists:products,id',
                'taking_quantity' => [
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) use ($request, $currentInventory) {
                        // Check if the new taking_quantity is not greater than the adjusted available stock
                        $inventory = Stock::where('product_id', $request->product_id)->first();
            
                        if ($inventory) {
                            // Calculate the available quantity considering the previously taken quantity
                            $adjustedBalance = $inventory->balance_quantity + $currentInventory->taking_quantity;
            
                            if ($value > $adjustedBalance) {
                                $fail('The taking quantity cannot exceed the available stock.');
                            }
                        } else {
                            $fail('Inventory record not found.');
                        }
                    },
                ],
                'service_id' => [
                    'nullable',
                    'exists:services,id',
                    function ($attribute, $value, $fail) use ($request) {
                        if (!$value && !$request->package_id) {
                            $fail('Either Service or Package is required.');
                        }
                    }
                ],
                'package_id' => [
                    'nullable',
                    'exists:packages,id',
                    function ($attribute, $value, $fail) use ($request) {
                        if (!$value && !$request->service_id) {
                            $fail('Either Service or Package is required.');
                        }
                    }
                ],
            ]);
            
            // Prepare data for update
            $productData = array_merge($request->all(), [
                'shop_id' => SHOP_ID
            ]);
            
            // Adjust the stock balance after validation
            $stock = Stock::where('shop_id', SHOP_ID)->where('product_id', $request->product_id)->first();
            
            if ($stock) {
                // Restore the previous quantity, then subtract the new taking quantity
                $stock->balance_quantity = $stock->balance_quantity + $currentInventory->taking_quantity - $request->taking_quantity;
                $stock->taking_quantity     =$request->taking_quantity;
                $stock->save();
                
                // Update the inventory entry with new data
                $currentInventory->update($productData);
            DB::commit();
                return redirect()->route('inventories.index')->with('success', 'Inventory updated successfully.');
            } else {
                return redirect()->back()->withErrors(['error' => 'Stock record not found.']);
            }

            
         
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction on failure
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventories.index')->with('success','Inventory deleted successfully');
    }
}
