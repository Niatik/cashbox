<?php

use App\Filament\Resources\ServiceResource;
use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function () {
    $this->actingAs(
        User::factory()->create()
    );
});

it('can render page', function () {
    $this->get(ServiceResource::getUrl('index'))->assertSuccessful();
});


