<?php

namespace LaravelEnso\DataExport\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataExport\Contracts\Notifies;
use LaravelEnso\DataExport\Models\DataExport;

class ExportDone extends Notification implements ShouldQueue
{
    use Dispatchable, Queueable;

    private DataExport $export;
    private Notifies $exporter;

    public function __construct(DataExport $export, Notifies $exporter)
    {
        $this->export = $export;
        $this->exporter = $exporter;
    }

    public function via()
    {
        return ['mail', 'broadcast', 'database'];
    }

    public function toBroadcast()
    {
        return (new BroadcastMessage($this->toArray() + [
            'level' => 'success',
            'title' => $this->exporter->emailSubject(),
        ]))->onQueue($this->queue);
    }

    public function toMail($notifiable)
    {
        $appName = Config::get('app.name');

        return (new MailMessage())
            ->subject("[ {$appName} ] {$this->exporter->emailSubject()}")
            ->markdown('laravel-enso/data-export::emails.export', [
                'name' => $notifiable->person->appellative(),
                'export' => $this->export,
            ]);
    }

    public function toArray()
    {
        return [
            'body' => $this->body(),
            'icon' => 'file-excel',
            'path' => '/import',
        ];
    }

    protected function body(): string
    {
        return __('Export available for download: :filename', [
            'filename' => $this->export->file->original_name,
        ]);
    }
}
