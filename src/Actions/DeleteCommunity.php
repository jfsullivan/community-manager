<?php

namespace jfsullivan\BrainTools\Actions;

class DeleteCommunity
{
    public function delete($community)
    {
        $community->purge();
    }
}
