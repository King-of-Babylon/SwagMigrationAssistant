<?php declare(strict_types=1);

namespace SwagMigrationAssistant\Migration\Media;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use SwagMigrationAssistant\Migration\Run\SwagMigrationRunDefinition;

class SwagMigrationMediaFileDefinition extends EntityDefinition
{
    public function getEntityName(): string
    {
        return 'swag_migration_media_file';
    }

    public function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new PrimaryKey(), new Required()),
            (new FkField('run_id', 'runId', SwagMigrationRunDefinition::class))->setFlags(new Required()),
            (new StringField('uri', 'uri'))->setFlags(new Required()),
            (new StringField('file_name', 'fileName'))->setFlags(new Required()),
            (new IntField('file_size', 'fileSize'))->setFlags(new Required()),
            (new IdField('media_id', 'mediaId'))->setFlags(new Required()),
            new BoolField('written', 'written'),
            new BoolField('processed', 'processed'),
            new BoolField('process_failure', 'processFailure'),
            new CreatedAtField(),
            new UpdatedAtField(),
            new ManyToOneAssociationField('run', 'run_id', SwagMigrationRunDefinition::class),
        ]);
    }

    public function getCollectionClass(): string
    {
        return SwagMigrationMediaFileCollection::class;
    }

    public function getEntityClass(): string
    {
        return SwagMigrationMediaFileEntity::class;
    }
}
