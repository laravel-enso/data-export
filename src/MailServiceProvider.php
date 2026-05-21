<?php

namespace LaravelEnso\DataExport;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\Mails\Preview\PreviewDefinition;
use LaravelEnso\Mails\Preview\PreviewRegistry;

class MailServiceProvider extends ServiceProvider
{
    public function boot(PreviewRegistry $registry): void
    {
        $registry->register(new PreviewDefinition(
            key: 'data-export-ready',
            name: 'Data Export Ready',
            view: 'laravel-enso/data-export::emails.export',
            data: [
                'name' => 'Jane',
                'export' => new class {
                    public int $entries = 1248;

                    public object $file;

                    public function __construct()
                    {
                        $this->file = (object) [
                            'original_name' => 'users-export.xlsx',
                        ];
                    }
                },
            ],
            section: PreviewDefinition::Core,
        ));

        $registry->register(new PreviewDefinition(
            key: 'data-export-error',
            name: 'Data Export Error',
            view: 'laravel-enso/data-export::emails.error',
            data: [
                'export' => new class {
                    public string $name = 'users-export.xlsx';
                },
                'message' => 'An error was encountered while generating users-export.xlsx',
            ],
            section: PreviewDefinition::Core,
        ));
    }
}
