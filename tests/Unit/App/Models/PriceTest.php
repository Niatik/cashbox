<?php

use App\Models\Price;
use App\Models\PriceItem;
use Illuminate\Database\Eloquent\Factories\Sequence;

it('has the price items', function () {
    //Arrange
    $price = Price::factory()
        ->has(
            PriceItem::factory()
                ->count(5)
                ->state(new Sequence(
                    fn (Sequence $sequence) => [
                        'name_item' => 'test'.$sequence->index * 10,
                        'time_item' => $sequence->index * 10,
                    ],
                ))
        )
        ->create();

    //Act
    $priceItems = $price->priceItems;

    //Assert
    expect($priceItems)
        ->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class)
        ->toHaveCount(5)
        ->each->toBeInstanceOf(PriceItem::class);
});
