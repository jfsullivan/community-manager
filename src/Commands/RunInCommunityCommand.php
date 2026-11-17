<?php

namespace jfsullivan\CommunityManager\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use jfsullivan\CommunityManager\Context\CommunityContext;
use jfsullivan\CommunityManager\Exceptions\CommunityNotFoundException;

/**
 * php artisan community:run acme "reports:send --force"
 * php artisan community:run 12 "cache:warm"
 * php artisan community:run all "reports:send"
 */
class RunInCommunityCommand extends Command
{
    protected $signature = 'community:run
        {community : The community id or slug, or "all" for every community}
        {artisan_command : The Artisan command line to run inside it, quoted}';

    protected $description = 'Run an Artisan command inside a community\'s context';

    public function handle(CommunityContext $context): int
    {
        $communities = $this->communities($context);

        if ($communities === null) {
            return self::FAILURE;
        }

        $exitCode = self::SUCCESS;

        foreach ($communities as $community) {
            $this->components->info(sprintf('Community #%s %s', $community->getKey(), $community->getAttribute('name')));

            $result = $context->run($community, fn () => Artisan::call($this->argument('artisan_command'), [], $this->output));

            $exitCode = max($exitCode, (int) $result);
        }

        return $exitCode;
    }

    /** @return iterable<int, Model>|null */
    protected function communities(CommunityContext $context): ?iterable
    {
        $value = (string) $this->argument('community');

        if ($value === 'all') {
            $communityClass = $context->communityModel();

            return $communityClass::query()->orderBy((new $communityClass)->getKeyName())->lazy();
        }

        try {
            return [$context->find($value)];
        } catch (CommunityNotFoundException $exception) {
            $this->components->error($exception->getMessage());

            return null;
        }
    }
}
