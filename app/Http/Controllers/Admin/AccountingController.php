<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AccountingController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        $payments = Order::paid()->whereBetween('paid_at', [$from, $to]);

        $byMethod = (clone $payments)->selectRaw('payment_method, SUM(total) as total, COUNT(*) as count')
            ->groupBy('payment_method')->get()->keyBy('payment_method');

        $income = (float) (clone $payments)->sum('total');
        $shippingIncome = (float) (clone $payments)->sum('shipping');
        $expenses = Expense::whereBetween('spent_at', [$from->toDateString(), $to->toDateString()]);
        $expenseTotal = (float) (clone $expenses)->sum('amount');

        $summary = [
            'income' => $income,
            'card' => (float) $byMethod->only(['stripe', 'paymob'])->sum('total'),
            'cash' => (float) ($byMethod['cod']->total ?? 0),
            'shipping' => $shippingIncome,
            'expenses' => $expenseTotal,
            'net' => $income - $expenseTotal,
            'unpaid_cod' => (float) Order::where('payment_method', 'cod')->where('payment_status', 'unpaid')
                ->where('status', '!=', 'cancelled')->sum('total'),
            'refunded' => (float) Order::where('payment_status', 'refunded')->whereBetween('updated_at', [$from, $to])->sum('total'),
        ];

        $transactions = (clone $payments)->latest('paid_at')
            ->when($request->method, fn ($q, $m) => $q->where('payment_method', $m))
            ->paginate(15, ['*'], 'tx')->withQueryString();

        $expenseList = (clone $expenses)->latest('spent_at')->get();

        return view('admin.accounting.index', compact('summary', 'byMethod', 'transactions', 'expenseList', 'from', 'to'));
    }

    /** Download the paid transactions as a CSV (opens in Excel). */
    public function export(Request $request)
    {
        [$from, $to] = $this->range($request);
        $orders = Order::paid()->whereBetween('paid_at', [$from, $to])->latest('paid_at')->get();

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Arabic names correctly
            fputcsv($out, ['Order', 'Date', 'Customer', 'Email', 'Phone', 'Method', 'Card', 'Transaction ID', 'Subtotal', 'Shipping', 'Total', 'Currency']);
            foreach ($orders as $o) {
                fputcsv($out, [
                    $o->number, $o->paid_at?->format('Y-m-d H:i'), $o->name, $o->email, $o->phone,
                    $o->payment_method_label, $o->card_last4 ? strtoupper($o->card_brand).' **** '.$o->card_last4 : '',
                    $o->transaction_id, $o->subtotal, $o->shipping, $o->total, $o->currency,
                ]);
            }
            fclose($out);
        }, 'payments-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv');
    }

    public function storeExpense(Request $request)
    {
        Expense::create($request->validate([
            'title' => 'required|string|max:150',
            'category' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'spent_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]));

        return back()->with('success', 'Expense added.');
    }

    public function destroyExpense(Expense $expense)
    {
        $expense->delete();

        return back()->with('success', 'Expense deleted.');
    }

    private function range(Request $request): array
    {
        $from = $request->from ? Carbon::parse($request->from)->startOfDay() : now()->startOfMonth();
        $to = $request->to ? Carbon::parse($request->to)->endOfDay() : now()->endOfDay();

        return [$from, $to];
    }
}
