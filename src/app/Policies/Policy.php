<?php

namespace LaravelEnso\DataExport\app\Policies;

use LaravelEnso\Core\app\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use LaravelEnso\DataExport\app\Models\DataExport;

class Policy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isAdmin() || $user->isSupervisor()) {
            return true;
        }
    }

    public function view(User $user, DataExport $dataExport)
    {
        return $this->ownsDataExport($user, $dataExport);
    }

    public function share(User $user, DataExport $dataExport)
    {
        return $this->ownsDataExport($user, $dataExport);
    }

    public function destroy(User $user, DataExport $dataExport)
    {
        return $this->ownsDataExport($user, $dataExport);
    }

    private function ownsDataExport(User $user, DataExport $dataExport)
    {
        return $user->id === (int) $dataExport->created_by;
    }
}
