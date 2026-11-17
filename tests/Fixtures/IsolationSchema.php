<?php

namespace jfsullivan\CommunityManager\Tests\Fixtures;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IsolationSchema
{
    public static function create(): void
    {
        foreach (['notes', 'shared_tags', 'unscoped_entries'] as $table) {
            Schema::create($table, function (Blueprint $table) {
                $table->id();
                $table->foreignId('community_id')->nullable();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }
}
