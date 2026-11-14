<?php

namespace jfsullivan\CommunityManager\Queue;

use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Log;
use jfsullivan\CommunityManager\Context\CommunityContext;
use jfsullivan\CommunityManager\Contracts\CommunityAware;
use jfsullivan\CommunityManager\Contracts\NotCommunityAware;
use jfsullivan\CommunityManager\Exceptions\CommunityNotFoundException;

/**
 * Jobs run inside the community they were dispatched in.
 *
 * Dispatching stores the current community's key in the job payload
 * (unless the job is NotCommunityAware, or queue.aware_by_default is off
 * and it isn't CommunityAware). The worker makes that community current
 * while the job runs, or explicitly none when the payload has no key, and
 * restores the previous state afterwards (so a sync job inside a request
 * hands the request its community back).
 */
class PropagatesCommunityContext
{
    public const PAYLOAD_KEY = 'communityManagerCommunityId';

    /** @var array<string, array{explicit: bool, community: mixed}> */
    protected array $snapshots = [];

    public function __construct(protected CommunityContext $context) {}

    public function register(Dispatcher $events): void
    {
        $this->registerPayloadHook();
        $this->registerWorkerListeners($events);
    }

    /** Payload hooks are static (shared by every app instance), so the hook reads the live context. */
    protected function registerPayloadHook(): void
    {
        Queue::createPayloadUsing(function ($connection, $queue, array $payload) {
            $job = $payload['data']['command'] ?? null;

            if (! is_object($job) || ! $this->isCommunityAware($job)) {
                return [];
            }

            $communityId = app(CommunityContext::class)->id();

            return $communityId === null ? [] : [self::PAYLOAD_KEY => $communityId];
        });
    }

    protected function registerWorkerListeners(Dispatcher $events): void
    {
        $events->listen(JobProcessing::class, function (JobProcessing $event) {
            $this->snapshots[$this->jobKey($event->job)] = $this->context->snapshot();

            $communityId = $event->job->payload()[self::PAYLOAD_KEY] ?? null;

            try {
                $this->context->set($communityId);
            } catch (CommunityNotFoundException) {
                // The community is gone (e.g. the job deletes it): run with none.
                Log::warning('community-manager: a queued job\'s community no longer exists; running it with no current community.', [
                    'job' => $event->job->resolveName(),
                    'community_id' => $communityId,
                ]);

                $this->context->set(null);
            }
        });

        foreach ([JobProcessed::class, JobFailed::class, JobExceptionOccurred::class] as $finished) {
            $events->listen($finished, function ($event) {
                $key = $this->jobKey($event->job);

                if (! array_key_exists($key, $this->snapshots)) {
                    return;
                }

                $snapshot = $this->snapshots[$key];
                unset($this->snapshots[$key]);

                $this->context->restore($snapshot);
            });
        }
    }

    protected function isCommunityAware(object $job): bool
    {
        $queued = $this->queuedClass($job);

        if (is_a($queued, NotCommunityAware::class, true)) {
            return false;
        }

        return is_a($queued, CommunityAware::class, true) || config('community-manager.queue.aware_by_default', true);
    }

    /**
     * What was queued: the mailable, notification, listener or broadcast
     * event Laravel wraps in a job of its own, otherwise the job itself, so
     * NotCommunityAware / CommunityAware on a queued mailable (etc.) count.
     *
     * @return object|class-string
     */
    protected function queuedClass(object $job): object|string
    {
        $wrapped = match (true) {
            $job instanceof SendQueuedMailable => $job->mailable,
            $job instanceof SendQueuedNotifications => $job->notification,
            $job instanceof CallQueuedListener => $job->class,
            $job instanceof BroadcastEvent => $job->event,
            default => null,
        };

        return is_object($wrapped) || (is_string($wrapped) && class_exists($wrapped)) ? $wrapped : $job;
    }

    protected function jobKey(mixed $job): string
    {
        return (string) ($job->uuid() ?? $job->getJobId() ?? spl_object_id($job));
    }
}
