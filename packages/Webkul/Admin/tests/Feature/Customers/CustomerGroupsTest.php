<?php

use Webkul\Customer\Models\CustomerGroup;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\get;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

afterEach(function () {
    /**
     * Cleaning up rows which are created.
     */
    CustomerGroup::query()
        ->whereNotBetween('id', [1, 3])
        ->delete();
});

it('should return the listing page of customer groups', function () {
    // Act and Assert
    $this->loginAsAdmin();

    get(route('admin.customers.groups.index'))
        ->assertOk()
        ->assertSeeText(trans('admin::app.customers.groups.index.title'))
        ->assertSeeText(trans('admin::app.customers.groups.index.create.create-btn'));
});

it('should store the newly created customers group', function () {
    // Act and Assert
    $this->loginAsAdmin();

    postJson(route('admin.customers.groups.store'), [
        'code' => $code = strtolower(fake()->words(1, true)),
        'name' => $name = fake()->name(),
    ])
        ->assertOk()
        ->assertSeeText(trans('admin::app.customers.groups.index.create.success'));

    $this->assertDatabaseHas('customer_groups', [
        'code' => $code,
        'name' => $name,
    ]);
});

it('should update the existing customers group', function () {
    // Arrange
    $customerGroup = CustomerGroup::factory()->create();

    // Act and Assert
    $this->loginAsAdmin();

    putJson(route('admin.customers.groups.update'), [
        'name' => $name = fake()->name(),
        'code' => $customerGroup->code,
        'id'   => $customerGroup->id,
    ])
        ->assertOk()
        ->assertSeeText(trans('admin::app.customers.groups.index.edit.success'));

    $this->assertDatabaseHas('customer_groups', [
        'name' => $name,
        'code' => $customerGroup->code,
        'id'   => $customerGroup->id,
    ]);
});

it('should delete the existing cutomers groups', function () {
    // Arrange
    $customerGroup = CustomerGroup::factory()->create([
        'is_user_defined' => true,
    ]);

    // Act and Assert
    $this->loginAsAdmin();

    deleteJson(route('admin.customers.groups.delete', $customerGroup->id))
        ->assertOk()
        ->assertSeeText(trans('admin::app.customers.groups.index.edit.delete-success'));

    $this->assertDatabaseMissing('customer_groups', [
        'id' => $customerGroup->id,
    ]);
});
