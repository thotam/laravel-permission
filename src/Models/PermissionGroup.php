<?php

namespace Spatie\Permission\Models;

use App\Models\Traits\Hrstamps;
use App\Models\Traits\Systems;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Permission\Support\Config;

/**
 * @property int $id
 * @property string $key
 * @property string $title
 * @property ?string $description
 * @property ?int $parent_id
 * @property int $order
 * @property ?string $vendor
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property-read ?PermissionGroup $parent
 * @property-read Collection<int, PermissionGroup> $children
 * @property-read Collection<int, Permission> $permissions
 */
class PermissionGroup extends Model
{
    use Hrstamps;
    use SoftDeletes;
    use Systems;

    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = Config::permissionGroupsTable() ?: parent::getTable();
    }

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Config::permissionGroupModel(), 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Config::permissionGroupModel(), 'parent_id')->orderBy('order');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Config::permissionModel(), 'permission_group_id');
    }
}
