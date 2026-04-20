<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\DataExport\Enums\Statuses;
use LaravelEnso\DataExport\Exceptions\Exception as ExportException;
use LaravelEnso\DataExport\Models\Export;
use LaravelEnso\Files\Models\File;
use LaravelEnso\Files\Models\Type;
use LaravelEnso\Users\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed()
            ->actingAs(User::first());
    }

    protected function tearDown(): void
    {
        File::query()->get()
            ->each(fn (File $file) => Storage::delete($file->path()));

        parent::tearDown();
    }

    #[Test]
    public function can_cancel_a_running_export(): void
    {
        $export = Export::factory()->create([
            'status' => Statuses::Processing,
        ]);

        $export->cancel();

        $this->assertSame(Statuses::Cancelled, $export->fresh()->status);
    }

    #[Test]
    public function cannot_cancel_a_non_running_export(): void
    {
        $export = Export::factory()->create([
            'status' => Statuses::Finalized,
        ]);

        $this->expectException(ExportException::class);

        $export->cancel();
    }

    #[Test]
    public function cancel_endpoint_cancels_a_running_export(): void
    {
        $export = Export::factory()->create([
            'status' => Statuses::Waiting,
        ]);

        $this->patch(route('export.cancel', $export, false))
            ->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('The export was cancelled successfully'),
            ]);

        $this->assertSame(Statuses::Cancelled, $export->fresh()->status);
    }

    #[Test]
    public function delete_is_blocked_for_running_exports(): void
    {
        $export = Export::factory()->create([
            'status' => Statuses::Processing,
        ]);

        $this->expectException(ExportException::class);

        $export->delete();
    }

    #[Test]
    public function deleting_a_deletable_export_also_deletes_its_attached_file(): void
    {
        $export = Export::factory()->create([
            'status' => Statuses::Finalized,
        ]);

        $file = $this->attachFileTo($export, 'export-delete.xlsx');

        $export->file()->associate($file)->save();

        Storage::assertExists($file->path());

        $export->delete();

        $this->assertDatabaseMissing('data_exports', ['id' => $export->id]);
        $this->assertDatabaseMissing('files', ['id' => $file->id]);
        Storage::assertMissing($file->path());
    }

    #[Test]
    public function progress_returns_null_when_total_is_zero_and_percentage_otherwise(): void
    {
        $emptyExport = Export::factory()->create([
            'entries' => 0,
            'total' => 0,
        ]);

        $startedExport = Export::factory()->create([
            'entries' => 25,
            'total' => 50,
        ]);

        $this->assertNull($emptyExport->progress());
        $this->assertSame(50, $startedExport->progress());
    }

    #[Test]
    public function status_reports_finalized_for_non_running_exports(): void
    {
        $waitingExport = Export::factory()->create([
            'status' => Statuses::Waiting,
        ]);

        $failedExport = Export::factory()->create([
            'status' => Statuses::Failed,
        ]);

        $this->assertSame(Statuses::Waiting, $waitingExport->status());
        $this->assertSame(Statuses::Finalized, $failedExport->status());
    }

    #[Test]
    public function purge_cancels_expired_running_exports_and_deletes_expired_deletable_exports(): void
    {
        Config::set('enso.exports.retainFor', 1);

        $expiredRunning = Export::factory()->create([
            'status' => Statuses::Processing,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $expiredFinalized = Export::factory()->create([
            'status' => Statuses::Finalized,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $file = $this->attachFileTo($expiredFinalized, 'export-purge.xlsx');
        $expiredFinalized->file()->associate($file)->save();

        $freshExport = Export::factory()->create([
            'status' => Statuses::Finalized,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('enso:data-export:purge')->assertExitCode(0);

        $this->assertSame(Statuses::Cancelled, $expiredRunning->fresh()->status);
        $this->assertNull($expiredFinalized->fresh());
        $this->assertNotNull($freshExport->fresh());
        Storage::assertMissing($file->path());
    }

    private function attachFileTo(Export $export, string $filename): File
    {
        $savedName = "saved-{$filename}";
        $path = Type::for(Export::class)->path($savedName);

        Storage::put($path, 'export');

        return File::attach($export, $savedName, $filename);
    }
}
