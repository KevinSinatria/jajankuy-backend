<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatController extends Controller
{
    public function getExpenseIncome()
    {
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subMonths(12)->startOfMonth();
        $dateFormat = 'YYYY-MM';
        $labelFormat = 'Mon YYYY';

        $data = DB::table('transactions')
            ->select(
                DB::raw("TO_CHAR(created_at, '{$dateFormat}') as period_key"),
                DB::raw("TO_CHAR(created_at, '{$labelFormat}') as label"),
                DB::raw("SUM(CASE WHEN type = 'Pemasukan' THEN amount ELSE 0 END) as income"),
                DB::raw("SUM(CASE WHEN type = 'Pengeluaran' THEN amount ELSE 0 END) as expenses")
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period_key', 'label')
            ->orderBy('period_key')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data statistik pemasukan dan pengeluaran.',
            'data' => $data
        ], 200);
    }

    public function getOverview()
    {
        $totalCustomer = User::where('role', 'customer')->count();
        $totalProduct = Product::count();
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now();
        $totalOrdersLast30Days = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $pendingOrders = Order::where('status', '!=', 'selesai')->count();
        $completedOrders = Order::where('status', 'selesai')->count();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data statistik overview.',
            'data' => [
                'total_customer' => $totalCustomer,
                'total_product' => $totalProduct,
                'total_orders_last_30_days' => $totalOrdersLast30Days,
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
            ]
        ], 200);
    }

    public function getProductSalesByCategory()
    {
        $rawData = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.status', 'selesai')
            ->select('categories.name', 'categories.slug', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->groupBy('categories.name', 'categories.slug')
            ->get();

        $total = $rawData->sum('total_quantity');

        $data = $rawData->map(function ($item) use ($total) {
            return [
                'name' => $item->name,
                'slug' => $item->slug,
                'percentage' => number_format(($item->total_quantity / $total) * 100, 2, '.', '')
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data statistik penjualan per kategori.',
            'data' => $data
        ]);
    }
}
