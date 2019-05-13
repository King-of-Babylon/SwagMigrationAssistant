<?php declare(strict_types=1);

namespace SwagMigrationAssistant\Profile\Shopware55\DataSelection\DataSet;

use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Profile\Shopware55\Shopware55Profile;

class CustomerGroupDataSet extends Shopware55DataSet
{
    public static function getEntity(): string
    {
        return DefaultEntities::CUSTOMER_GROUP;
    }

    public function supports(string $profileName, string $entity): bool
    {
        return $profileName === Shopware55Profile::PROFILE_NAME && $entity === self::getEntity();
    }

    public function getApiRoute(): string
    {
        return 'SwagMigrationCustomerGroups';
    }

    public function getExtraQueryParameters(): array
    {
        return [];
    }
}
