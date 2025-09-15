<?php

namespace App\Console\Commands;

use App\Mail\LowStockMail;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckLowStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifyca si hay productos con poco stock y envia notificacion';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lowStockProducts = Product::where('stock', '<=',5)->get();

        if($lowStockProducts->isNotEmpty()){
            Mail::to('nicolasmoron15@gmail.com')->send(new LowStockMail($lowStockProducts));
            $this->info('Notificación de bajo stock enviada.');
        } else {
            $this->info('No hay productos con bajo stock.');
        }
    }
}
