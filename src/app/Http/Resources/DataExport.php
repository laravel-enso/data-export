<?php

namespace LaravelEnso\DataExport\app\Http\Resources;

use LaravelEnso\TrackWho\app\Traits\CreatedBy;
use Illuminate\Http\Resources\Json\JsonResource;
use LaravelEnso\TrackWho\app\Http\Resources\TrackWho;

class DataExport extends JsonResource
{
    use CreatedBy;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'entries' => $this->entries,
            'since' => $this->created_at,
            'owner' => $this->whenLoaded(
                'createdBy', new TrackWho($this->createdBy)
            ),
        ];
    }
}
