<?php

namespace LaravelEnso\DataExport\Contracts;

interface NotifiesEntities extends Notifies
{
    public function entities(): array;
}
