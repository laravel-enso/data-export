<?php

namespace LaravelEnso\DataExport\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use LaravelEnso\DataExport\Contracts\Notifies;
use LaravelEnso\DataExport\Models\DataExport;

class ExportError extends Notification implements ShouldQueue
{
    use Queueable;

    private DataExport $export;
    private Notifies $exporter;
    private string $subject;

    public function __construct(DataExport $export, Notifies $exporter)
    {
        $this->export = $export;
        $this->exporter = $exporter;
        $this->subject = $exporter->emailSubject($export);
    }

    public function via()
    {
        return ['mail', 'broadcast', 'database'];
    }

    public function toBroadcast()
    {
        return (new BroadcastMessage($this->toArray() + [
            'level' => 'error',
            'title' => $this->subject(),
        ]))->onQueue($this->queue);
    }

    public function toArray()
    {
        return [
            'body' => $this->subject(),
            'path' => '#',
            'icon' => 'file-excel',
        ];
    }

    private function subject(): string
    {
        return __('An error was encountered while generationg :export', [
            'export' => $this->subject,
        ]);
    }
}
