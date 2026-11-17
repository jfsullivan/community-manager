<?php

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use jfsullivan\CommunityManager\Contracts\CommunityAware;
use jfsullivan\CommunityManager\Contracts\NotCommunityAware;
use jfsullivan\CommunityManager\Facades\CommunityContext;
use jfsullivan\CommunityManager\Jobs\Middleware\RunInCommunity;
use jfsullivan\CommunityManager\Queue\PropagatesCommunityContext;

class RecordsCommunityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public static array $seen = [];

    public function handle(): void
    {
        static::$seen[] = CommunityContext::id();
    }
}

class PlatformJob extends RecordsCommunityJob implements NotCommunityAware {}

class OptedInJob extends RecordsCommunityJob implements CommunityAware {}

class PinnedJob extends RecordsCommunityJob
{
    public function __construct(public int $communityId) {}

    public function middleware(): array
    {
        return [new RunInCommunity($this->communityId)];
    }
}

class QueuedCommunityMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function content(): Content
    {
        return new Content(htmlString: '<p>Hello</p>');
    }
}

class QueuedPlatformMail extends QueuedCommunityMail implements NotCommunityAware {}

beforeEach(function () {
    RecordsCommunityJob::$seen = [];
    config()->set('mail.default', 'array');
    config()->set('queue.default', 'sync');
});

describe('queued jobs', function () {
    it('run inside the community they were dispatched in, and hand it back', function () {
        $a = createCommunity();

        CommunityContext::run($a, function () use ($a) {
            RecordsCommunityJob::dispatch();

            expect(CommunityContext::id())->toBe($a->id);
        });

        RecordsCommunityJob::dispatch();

        expect(RecordsCommunityJob::$seen)->toBe([$a->id, null]);
    });

    it('carry the community in the payload', function () {
        $a = createCommunity();
        $payloads = [];
        app('events')->listen(JobProcessing::class, function ($event) use (&$payloads) {
            $payloads[] = $event->job->payload();
        });

        // A block, not an arrow function: a returned PendingDispatch would only dispatch after run() ends.
        CommunityContext::run($a, function () {
            RecordsCommunityJob::dispatch();
        });

        expect($payloads[0][PropagatesCommunityContext::PAYLOAD_KEY])->toBe($a->id);
    });

    it('skip jobs that opt out, and run them with no community', function () {
        $a = createCommunity();

        CommunityContext::run($a, function () use ($a) {
            PlatformJob::dispatch();

            expect(CommunityContext::id())->toBe($a->id);
        });

        expect(RecordsCommunityJob::$seen)->toBe([null]);
    });

    it('read the opt-out from a queued mailable, not the job Laravel wraps it in', function () {
        $a = createCommunity();
        $payloads = [];
        app('events')->listen(JobProcessing::class, function ($event) use (&$payloads) {
            $payloads[] = $event->job->payload();
        });

        CommunityContext::run($a, function () {
            Mail::to('someone@example.com')->queue(new QueuedCommunityMail);
            Mail::to('someone@example.com')->queue(new QueuedPlatformMail);
        });

        expect($payloads)->toHaveCount(2)
            ->and($payloads[0][PropagatesCommunityContext::PAYLOAD_KEY] ?? null)->toBe($a->id)
            ->and($payloads[1])->not->toHaveKey(PropagatesCommunityContext::PAYLOAD_KEY);
    });

    it('only propagate opted-in jobs when not aware by default', function () {
        config()->set('community-manager.queue.aware_by_default', false);
        $a = createCommunity();

        CommunityContext::run($a, function () {
            RecordsCommunityJob::dispatch();
            OptedInJob::dispatch();
        });

        expect(RecordsCommunityJob::$seen)->toBe([null, $a->id]);
    });

    it('run in a named community with the RunInCommunity middleware', function () {
        $a = createCommunity();
        $b = createCommunity();

        CommunityContext::run($a, function () use ($b) {
            PinnedJob::dispatch($b->id);
        });

        expect(RecordsCommunityJob::$seen)->toBe([$b->id]);
    });

    it('run with no community when theirs no longer exists', function () {
        $a = createCommunity();

        CommunityContext::run($a, function () use ($a) {
            DB::table('communities')->where('id', $a->id)->delete();
            RecordsCommunityJob::dispatch();
        });

        expect(RecordsCommunityJob::$seen)->toBe([null]);
    });

    it('hand the previous state back after a failure', function () {
        $a = createCommunity();

        CommunityContext::run($a, function () use ($a) {
            try {
                dispatch(new class implements NotCommunityAware, ShouldQueue
                {
                    use Dispatchable, InteractsWithQueue, Queueable;

                    public function handle(): void
                    {
                        throw new RuntimeException('job failed');
                    }
                });
            } catch (RuntimeException) {
            }

            expect(CommunityContext::id())->toBe($a->id);
        });
    });
});

describe('community:run', function () {
    beforeEach(function () {
        Artisan::command('test:which-community', function () {
            $this->line('community='.(CommunityContext::id() ?? 'none'));
        });
    });

    it('runs a command inside a community by id or slug', function () {
        $a = createCommunity();
        $a->forceFill(['slug' => 'acme'])->save();

        $this->artisan('community:run', ['community' => 'acme', 'artisan_command' => 'test:which-community'])
            ->expectsOutputToContain('community='.$a->id)
            ->assertSuccessful();

        $this->artisan('community:run', ['community' => (string) $a->id, 'artisan_command' => 'test:which-community'])
            ->expectsOutputToContain('community='.$a->id)
            ->assertSuccessful();

        expect(CommunityContext::id())->toBeNull();
    });

    it('runs a command in every community', function () {
        $a = createCommunity();
        $b = createCommunity();

        $this->artisan('community:run', ['community' => 'all', 'artisan_command' => 'test:which-community'])
            ->expectsOutputToContain('community='.$a->id)
            ->expectsOutputToContain('community='.$b->id)
            ->assertSuccessful();
    });

    it('fails for an unknown community', function () {
        $this->artisan('community:run', ['community' => 'nope', 'artisan_command' => 'test:which-community'])
            ->assertFailed();
    });
});
