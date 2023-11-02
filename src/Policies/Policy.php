<?php

namespace LaravelEnso\DataExport\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use LaravelEnso\DataExport\Models\Export;
use LaravelEnso\Users\Models\User;

class Policy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        return $user->isSuperior();
    }

    public function view(User $user, Export $export)
    {
        return $this->ownsDataExport($user, $export);
    }

    public function share(User $user, Export $export)
    {
        return $this->ownsDataExport($user, $export);
    }

    public function destroy(User $user, Export $export)
    {
        return $this->ownsDataExport($user, $export);
    }

    private function ownsDataExport(User $user, Export $export)
    {
        return $user->id === (int) $export->created_by;
    }
}
