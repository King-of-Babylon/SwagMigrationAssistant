<?php declare(strict_types=1);

namespace SwagMigrationAssistant\Command\Event;

use Symfony\Component\EventDispatcher\Event;

class MigrationMediaDownloadAdvanceEvent extends Event
{
    public const EVENT_NAME = 'migration.media.download.advance';
}
