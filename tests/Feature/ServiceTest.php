<?php

use App\Filament\Resources\ServiceResource;
use App\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;

use Livewire\Livewire;
use function Pest\Livewire\livewire;


beforeEach(function () {
    $this->actingAs(
        User::factory()->create()
    );
});

it('can render page', function () {
    $this->get(ServiceResource::getUrl('index'))->assertSuccessful();
});

it('can list services', function () {
    $services = Service::factory()->count(10)->create();

    livewire(ServiceResource\Pages\ListServices::class)
        ->assertCanSeeTableRecords($services);
});


