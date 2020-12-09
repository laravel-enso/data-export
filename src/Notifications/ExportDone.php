<?php

namespace LaravelEnso\DataExport\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataExport\Models\DataExport;

class ExportDone extends Notification implements ShouldQueue
{
    use Dispatchable, Queueable;

    private DataExport $export;
    private ?string $subject;

    public function __construct(DataExport $export, ?string $subject)
    {
        $this->export = $export;
        $this->subject = $subject ?? __('Your export is done');
    }

    public function via()
    {
        return ['mail', 'broadcast', 'database'];
    }

    public function toBroadcast()
    {
        return (new BroadcastMessage($this->toArray() + [
            'level' => 'success',
            'title' => $this->subject,
        ]))->onQueue($this->queue);
    }

    public function toMail($notifiable)
    {
        $appName = Config::get('app.name');

        return (new MailMessage())
            ->subject("[ {$appName} ] {$this->subject}")
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
