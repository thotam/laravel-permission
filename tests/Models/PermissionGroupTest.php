<?php

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\PermissionGroup;
use Spatie\Permission\Models\Role;

it('loads parent, children and permissions', function () {
    $root = PermissionGroup::create(['key' => 'root', 'title' => 'Root', 'order' => 0]);
    $second = PermissionGroup::create(['key' => 'child-b', 'title' => 'Child B', 'parent_id' => $root->id, 'order' => 2]);
    $child = PermissionGroup::create(['key' => 'child-a', 'title' => 'Child A', 'parent_id' => $root->id, 'order' => 1]);

    $first = Permission::create(['name' => 'group-permission-1', 'permission_group_id' => $child->id]);
    Permission::create(['name' => 'group-permission-2', 'permission_group_id' => $child->id]);

    expect($child->parent->is($root))->toBeTrue()
        ->and($root->children->pluck('key')->all())->toBe(['child-a', 'child-b'])
        ->and($second->children)->toHaveCount(0)
        ->and($child->permissions)->toHaveCount(2)
        ->and($first->permissionGroup->is($child))->toBeTrue();
});

it('uses configured table name', function () {
    expect((new PermissionGroup)->getTable())->toBe('permission_groups');

    config()->set('permission.table_names.permission_groups', 'custom_permission_groups');

    expect((new PermissionGroup)->getTable())->toBe('custom_permission_groups');
});

it('links roles to a permission group', function () {
    $root = PermissionGroup::create(['key' => 'root-roles', 'title' => 'Root', 'order' => 0]);
    $role = Role::create(['name' => 'grouped-role', 'permission_group_id' => $root->id]);
    Role::create(['name' => 'ungrouped-role']);

    expect($role->permissionGroup->is($root))->toBeTrue()
        ->and($root->roles->pluck('name')->all())->toBe(['grouped-role']);
});
