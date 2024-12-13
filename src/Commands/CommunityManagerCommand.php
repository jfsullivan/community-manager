<?php

namespace jfsullivan\CommunityManager\Commands;

use Illuminate\Console\Command;

class CommunityManagerCommand extends Command
{
    public $signature = 'community-manager';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
