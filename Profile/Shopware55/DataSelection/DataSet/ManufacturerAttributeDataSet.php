<?php declare(strict_types=1);

namespace SwagMigrationAssistant\Profile\Shopware55\DataSelection\DataSet;

use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Profile\Shopware55\Shopware55Profile;

class ManufacturerAttributeDataSet extends Shopware55DataSet
{
    public static function getEntity(): string
    {
        return DefaultEntities::PRODUCT_MANUFACTURER_CUSTOM_FIELD;
    }

    public function supports(string $profileName): bool
    {
        return $profileName === Shopware55Profile::PROFILE_NAME;
    }

    public function getApiRoute(): string
    {
        return 'SwagMigrationAttributes';
    }

    public function getExtraQueryParameters(): array
    {
        return [
            'attribute_table' => 's_articles_supplier_attributes',
        ];
    }
}
