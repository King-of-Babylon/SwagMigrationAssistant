<?php declare(strict_types=1);

namespace SwagMigrationNext\Gateway\Shopware55\Api\Reader;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Shopware55ReaderNotFoundException extends ShopwareHttpException
{
    protected $code = 'SWAG-MIGRATION-SHOPWARE55-READER-NOT-FOUND';

    public function __construct(string $entityName, $code = 0, Throwable $previous = null)
    {
        $message = sprintf('Shopware55 reader for "%s" not found', $entityName);
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
