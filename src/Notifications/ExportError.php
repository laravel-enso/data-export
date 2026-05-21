<?php

namespace LaravelEnso\DataExport\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use LaravelEnso\DataExport\Models\Export;

class ExportError extends Notification implements ShouldQueue
{
    use Queueable;

    private string $subject;

    public function __construct(private Export $export)
    {
        $this->subject ??= Lang::get('Export error');
    }

    public function via()
    {
        return ['mail', 'broadcast', 'database'];
    }

    public function toBroadcast()
    {
        return (new BroadcastMessage($this->toArray() + [
            'level' => 'error',
            'title' => Lang::get('Export error'),
        ]))->onQueue($this->queue);
    }

    public function toMail()
    {
        $appName = Config::get('app.name');

        return (new MailMessage())
            ->subject("[ {$appName} ] {$this->subject}")
            ->markdown('laravel-enso/data-export::emails.error', [
                'export' => $this->export,
                'message' => Lang::get('An error was encountered while generating :export', [
                    'export' => $this->export->name,
                ]),
            ]);
    }

    public function toArray()
    {
        return [
            'body' => $this->subject,
            'path' => '#',
            'icon' => 'file-excel',
        ];
    }
}
