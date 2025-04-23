<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\TradeTransaction;
use App\Http\Controllers\WalletBalanceDeductionController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessPendingDeductions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:process-deductions {--debug : Display detailed debug information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process wallet deductions for completed orders and trades';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting to process pending deductions...');
        $debug = $this->option('debug');
        $controller = new WalletBalanceDeductionController();
        $successCount = 0;
        $errorCount = 0;

        // Process completed orders with no deduction
        $this->info('Processing orders...');
        $orders = Order::where('status', 'Completed')
            ->where('wallet_deduction_processed', false)
            ->get();
            
        $this->info("Found {$orders->count()} orders to process");
        
        if ($debug) {
            $this->table(
                ['ID', 'Seller Code', 'Sub Total'],
                $orders->map(fn($order) => [$order->id, $order->seller_code, $order->sub_total])
            );
        }
        
        foreach ($orders as $order) {
            $this->info("Processing order {$order->id}...");
            $result = $controller->processOrderDeduction($order);
            if ($result['success']) {
                $successCount++;
                $order->wallet_deduction_processed = true;
                $order->save();
                $this->info("  ✓ Deduction successful - Transaction ID: {$result['transaction']['id']}");
            } else {
                $errorCount++;
                $this->error("  ✗ Failed to process order {$order->id}: {$result['message']}");
            }
        }

        // Process completed trades with no deduction
        $this->info('Processing trades...');
        $trades = TradeTransaction::where('status', 'completed')
            ->where('wallet_deduction_processed', false)
            ->get();
            
        $this->info("Found {$trades->count()} trades to process");
        
        if ($debug && $trades->count() > 0) {
            $this->table(
                ['ID', 'Seller Code', 'Product ID', 'Additional Cash'],
                $trades->map(fn($trade) => [$trade->id, $trade->seller_code, $trade->seller_product_id, $trade->additional_cash])
            );
        }
        
        foreach ($trades as $trade) {
            $this->info("Processing trade {$trade->id}...");
            $result = $controller->processTradeDeduction($trade);
            if ($result['success']) {
                $successCount++;
                $trade->wallet_deduction_processed = true;
                $trade->save();
                $this->info("  ✓ Deduction successful - Transaction ID: {$result['transaction']['id']}");
            } else {
                $errorCount++;
                $this->error("  ✗ Failed to process trade {$trade->id}: {$result['message']}");
            }
        }

        $this->info("Processing completed: {$successCount} successful, {$errorCount} failed");
        return Command::SUCCESS;
    }
}
