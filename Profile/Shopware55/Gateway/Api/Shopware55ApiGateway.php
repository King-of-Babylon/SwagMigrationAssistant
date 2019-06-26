<?php declare(strict_types=1);

namespace SwagMigrationAssistant\Profile\Shopware55\Gateway\Api;

use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Migration\EnvironmentInformation;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Migration\Profile\ReaderInterface;
use SwagMigrationAssistant\Profile\Shopware55\Gateway\Shopware55GatewayInterface;
use SwagMigrationAssistant\Profile\Shopware55\Gateway\TableReaderInterface;
use SwagMigrationAssistant\Profile\Shopware55\Shopware55Profile;

class Shopware55ApiGateway implements Shopware55GatewayInterface
{
    public const GATEWAY_NAME = 'api';

    /**
     * @var ReaderInterface
     */
    private $apiReader;

    /**
     * @var ReaderInterface
     */
    private $environmentReader;

    /**
     * @var TableReaderInterface
     */
    private $tableReader;

    public function __construct(
        ReaderInterface $apiReader,
        ReaderInterface $environmentReader,
        TableReaderInterface $tableReader
    ) {
        $this->apiReader = $apiReader;
        $this->environmentReader = $environmentReader;
        $this->tableReader = $tableReader;
    }

    public function supports(string $gatewayIdentifier): bool
    {
        return $gatewayIdentifier === Shopware55Profile::PROFILE_NAME . self::GATEWAY_NAME;
    }

    public function read(MigrationContextInterface $migrationContext): array
    {
        return $this->apiReader->read($migrationContext);
    }

    public function readEnvironmentInformation(MigrationContextInterface $migrationContext): EnvironmentInformation
    {
        $environmentData = $this->environmentReader->read($migrationContext);
        $environmentDataArray = $environmentData['environmentInformation'];

        if (empty($environmentDataArray)) {
            return new EnvironmentInformation(
                Shopware55Profile::SOURCE_SYSTEM_NAME,
                Shopware55Profile::SOURCE_SYSTEM_VERSION,
                '',
                [],
                [],
                $environmentData['warning']['code'],
                $environmentData['warning']['detail'],
                $environmentData['error']['code'],
                $environmentData['error']['detail']
            );
        }

        if (!isset($environmentDataArray['translations'])) {
            $environmentDataArray['translations'] = 0;
        }

        $updateAvailable = false;
        if (isset($environmentData['environmentInformation']['updateAvailable'])) {
            $updateAvailable = $environmentData['environmentInformation']['updateAvailable'];
        }

        $totals = [
            DefaultEntities::CATEGORY => $environmentDataArray['categories'],
            DefaultEntities::PRODUCT => $environmentDataArray['products'],
            DefaultEntities::CUSTOMER => $environmentDataArray['customers'],
            DefaultEntities::ORDER => $environmentDataArray['orders'],
            DefaultEntities::MEDIA => $environmentDataArray['assets'],
            DefaultEntities::CUSTOMER_GROUP => $environmentDataArray['customerGroups'],
            DefaultEntities::PROPERTY_GROUP_OPTION => $environmentDataArray['configuratorOptions'],
            DefaultEntities::TRANSLATION => $environmentDataArray['translations'],
            DefaultEntities::NUMBER_RANGE => $environmentDataArray['numberRanges'],
            DefaultEntities::CURRENCY => $environmentDataArray['currencies'],
            DefaultEntities::NEWSLETTER_RECIPIENT => $environmentDataArray['newsletterRecipients'],
        ];
        $credentials = $migrationContext->getConnection()->getCredentialFields();

        return new EnvironmentInformation(
            Shopware55Profile::SOURCE_SYSTEM_NAME,
            $environmentDataArray['shopwareVersion'],
            $credentials['endpoint'],
            $totals,
            $environmentDataArray['additionalData'],
            $environmentData['warning']['code'],
            $environmentData['warning']['detail'],
            $environmentData['error']['code'],
            $environmentData['error']['detail'],
            $updateAvailable
        );
    }

    public function readTable(MigrationContextInterface $migrationContext, string $tableName, array $filter = []): array
    {
        return $this->tableReader->read($migrationContext, $tableName, $filter);
    }
}
