<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class AuditTrailService
{
    public static function logUser(?User $user, string $action, ?string $description = null, ?string $targetType = null, ?int $targetId = null): void
    {
        if (! $user) {
            return;
        }

        $actorType = $user->isAdmin() ? 'admin' : ($user->isHr() ? 'hr' : 'user');

        self::log(
            $actorType,
            (int) $user->id,
            $user->name,
            $user->email,
            $action,
            $description,
            $targetType,
            $targetId
        );
    }

    public static function logEmployee(?Employee $employee, string $action, ?string $description = null, ?string $targetType = null, ?int $targetId = null): void
    {
        if (! $employee) {
            return;
        }

        self::log(
            'employee',
            (int) $employee->id,
            $employee->name,
            $employee->email ?? null,
            $action,
            $description,
            $targetType,
            $targetId
        );
    }

    public static function logSystem(string $action, ?string $description = null, ?string $targetType = null, ?int $targetId = null): void
    {
        self::log('system', null, 'System', null, $action, $description, $targetType, $targetId);
    }

    protected static function log(
        string $actorType,
        ?int $actorId,
        ?string $actorName,
        ?string $actorEmail,
        string $action,
        ?string $description,
        ?string $targetType,
        ?int $targetId
    ): void {
        /** @var Request|null $request */
        $request = null;

        if (! app()->runningInConsole()) {
            $request = request();
        }

        AuditTrail::create([
            'actor_type'  => $actorType,
            'actor_id'    => $actorId,
            'actor_name'  => $actorName,
            'actor_email' => $actorEmail,
            'action'      => $action,
            'description' => $description,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'ip_address'  => $request?->ip(),
            'user_agent'  => $request?->userAgent(),
        ]);
    }
}

