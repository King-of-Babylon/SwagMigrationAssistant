<?php declare(strict_types=1);

namespace SwagMigrationNext\Profile\Shopware55\Converter;

use Shopware\Core\Framework\Struct\Uuid;
use Shopware\Core\System\Tax\TaxDefinition;
use SwagMigrationNext\Profile\Shopware55\ConvertStruct;

class TaxConverter implements ConverterInterface
{
    public function supports(): string
    {
        return TaxDefinition::getEntityName();
    }

    public function convert(array $data, array $additionalRelationData = []): ConvertStruct
    {
        $converted = [];
        $oldId = $data['id'];
        $uuid = Uuid::uuid4()->getHex();
        $converted['id'] = $uuid;

        $converted['taxRate'] = (float) $data['tax'];
        unset($data['tax']);

        $converted['name'] = $data['description'];
        unset($data['description']);

        return new ConvertStruct($converted, $data, $oldId, $uuid);
    }
}
