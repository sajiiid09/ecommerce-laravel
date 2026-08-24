<?php
namespace App\Livewire\Pages\Admin;
use App\Enums\ProductStatus; use App\Models\{InventoryItem,Product,ProductVariant}; use Illuminate\Support\Facades\{DB,Schema}; use Livewire\Attributes\Layout; use Livewire\Component;
#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public function render(){ $orders=Schema::hasTable('orders')?DB::table('orders')->count():0; $customers=Schema::hasTable('users')?DB::table('users')->count():0; $sales=Schema::hasTable('orders')?(int)DB::table('orders')->sum(DB::getSchemaBuilder()->hasColumn('orders','total')?'total':'id'):0; return view('livewire.pages.admin.dashboard',['stats'=>['products'=>Product::count(),'published'=>Product::where('status',ProductStatus::Published->value)->count(),'low_stock'=>InventoryItem::whereColumn('quantity_on_hand','<=','low_stock_threshold')->count(),'variants'=>ProductVariant::count(),'orders'=>$orders,'customers'=>$customers,'sales'=>$sales]]); }
}
