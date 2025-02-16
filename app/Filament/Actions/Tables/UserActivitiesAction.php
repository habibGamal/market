<?php

namespace Filament\Actions\Tables;

use Filament\Tables\Actions\Action;

class UserActivitiesAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'userActivities';
    }

    protected function setUp(): void
    {
        parent::setUp();

        // TODO: Add your setup logic here
    }
}
