<?php declare(strict_types=1);

namespace SwagMigrationNext\Test;

use Shopware\Core\Checkout\Cart\Price\PriceRounding;
use Shopware\Core\Checkout\Cart\Tax\PercentageTaxRuleCalculator;
use Shopware\Core\Checkout\Cart\Tax\TaxCalculator;
use Shopware\Core\Checkout\Cart\Tax\TaxRuleCalculator;
use Shopware\Core\Framework\DataAbstractionLayer\RepositoryInterface;
use SwagMigrationNext\Gateway\GatewayFactoryRegistry;
use SwagMigrationNext\Gateway\Shopware55\Api\Shopware55ApiFactory;
use SwagMigrationNext\Migration\Logging\LoggingService;
use SwagMigrationNext\Migration\Asset\MediaFileServiceInterface;
use SwagMigrationNext\Migration\Service\MigrationCollectService;
use SwagMigrationNext\Migration\Service\MigrationCollectServiceInterface;
use SwagMigrationNext\Profile\ProfileRegistry;
use SwagMigrationNext\Profile\Shopware55\Converter\AssetConverter;
use SwagMigrationNext\Profile\Shopware55\Converter\CategoryConverter;
use SwagMigrationNext\Profile\Shopware55\Converter\ConverterRegistry;
use SwagMigrationNext\Profile\Shopware55\Converter\CustomerConverter;
use SwagMigrationNext\Profile\Shopware55\Converter\OrderConverter;
use SwagMigrationNext\Profile\Shopware55\Converter\ProductConverter;
use SwagMigrationNext\Profile\Shopware55\Converter\TranslationConverter;
use SwagMigrationNext\Profile\Shopware55\ConverterHelperService;
use SwagMigrationNext\Profile\Shopware55\Mapping\Shopware55MappingService;
use SwagMigrationNext\Profile\Shopware55\Shopware55Profile;
use SwagMigrationNext\Test\Mock\DummyCollection;
use SwagMigrationNext\Test\Mock\Gateway\Dummy\Local\DummyLocalFactory;
use SwagMigrationNext\Test\Mock\Profile\Dummy\DummyInvalidCustomerConverter;

trait MigrationServicesTrait
{
    protected function getMigrationCollectService(
        RepositoryInterface $migrationDataRepo,
        Shopware55MappingService $mappingService,
        MediaFileServiceInterface $mediaFileService,
        RepositoryInterface $loggingRepo
    ): MigrationCollectServiceInterface {
        $loggingService = new LoggingService($loggingRepo);
        $priceRounding = new PriceRounding(2);
        $taxRuleCalculator = new TaxRuleCalculator($priceRounding);
        $converterRegistry = new ConverterRegistry(
            new DummyCollection(
                [
                    new ProductConverter($mappingService, new ConverterHelperService(), $mediaFileService, $loggingService),
                    new TranslationConverter($mappingService, new ConverterHelperService(), $loggingService),
                    new CategoryConverter($mappingService, new ConverterHelperService(), $loggingService),
                    new AssetConverter($mappingService, new ConverterHelperService(), $mediaFileService),
                    new CustomerConverter($mappingService, new ConverterHelperService(), $loggingService),
                    new CustomerConverter($mappingService, new ConverterHelperService(), $loggingService),
                    new OrderConverter(
                        $mappingService,
                        new ConverterHelperService(),
                        new TaxCalculator(
                            $priceRounding,
                            [
                                new PercentageTaxRuleCalculator($taxRuleCalculator),
                                $taxRuleCalculator,
                            ]
                        ),
                        $loggingService
                    ),
                    new DummyInvalidCustomerConverter($mappingService, new ConverterHelperService(), $loggingService),
                ]
            )
        );

        $profileRegistry = new ProfileRegistry(new DummyCollection([
            new Shopware55Profile(
                $migrationDataRepo,
                $converterRegistry,
                $mediaFileService,
                $loggingService
            ),
        ]));

        $gatewayFactoryRegistry = new GatewayFactoryRegistry(new DummyCollection([
            new Shopware55ApiFactory(),
            new DummyLocalFactory(),
        ]));

        return new MigrationCollectService($profileRegistry, $gatewayFactoryRegistry, $loggingService);
    }
}
