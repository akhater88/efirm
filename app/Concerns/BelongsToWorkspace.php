<?php

namespace App\Concerns;

use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Adds automatic workspace scoping to any tenant-scoped model.
 *
 * Usage: add `use BelongsToWorkspace;` to the model.
 * Requires: a `workspace_id` column on the model's table.
 *
 * This trait:
 * 1. Adds a global scope that filters all queries by the current workspace
 * 2. Adds a `workspace()` BelongsTo relationship
 * 3. Auto-sets `workspace_id` on creating if not explicitly provided
 */
trait BelongsToWorkspace
{
    public static function bootBelongsToWorkspace(): void
    {
        static::addGlobalScope('workspace', function (Builder $builder) {
            $workspaceId = static::resolveCurrentWorkspaceId();

            if ($workspaceId) {
                $builder->where(
                    $builder->getModel()->getTable().'.workspace_id',
                    $workspaceId
                );
            }
        });

        static::creating(function (Model $model) {
            if (empty($model->workspace_id)) {
                $model->workspace_id = static::resolveCurrentWorkspaceId();
            }
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    protected static function resolveCurrentWorkspaceId(): ?string
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return null;
        }

        $user = auth()->user();

        if ($user && method_exists($user, 'currentWorkspace')) {
            return $user->currentWorkspace()?->id;
        }

        return session('current_workspace_id');
    }
}
