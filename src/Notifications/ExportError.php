<?php

namespace LaravelEnso\DataExport\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataExport\Models\DataExport;

class ExportError extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private DataExport $export,
        private string $subject = __('Export error')
    ) {
    }

    public function via()
    {
        return ['mail', 'broadcast', 'database'];
    }

    public function toBroadcast()
    {
        return (new BroadcastMessage($this->toArray() + [
            'level' => 'error',
            'title' => __('Export error'),
        ]))->onQueue($this->queue);
    }

    public function toMail()
    {
        $appName = Config::get('app.name');

        return (new MailMessage())
            ->subject("[ {$appName} ] {$this->subject}")
            ->line(__('An error was encountered while generationg :export', [
                'export' => $this->export->name,
            ]));
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
