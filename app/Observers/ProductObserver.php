<?php

namespace App\Observers;

use App\Mail\LowStockMail;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Si cambió el stock
        if ($product->isDirty('stock') && $product->stock <= 5) {

            //Enviar notificación por email

            Mail::to('nicolasmoron15@gmail.com')->send(new LowStockMail(collect([$product])));




            // notifyWhatsApp("⚠️ El producto {$product->name} tiene poco stock ({$product->stock} unidades).");
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
