<?php

namespace App\Events;

use App\Models\ProductOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductOrderDeleting
{
    use Dispatchable, SerializesModels;

    public function __construct(public ProductOrder $productOrder) {}
}
