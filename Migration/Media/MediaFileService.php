<?php declare(strict_types=1);

namespace SwagMigrationNext\Migration\Media;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use SwagMigrationNext\Migration\MigrationContextInterface;

class MediaFileService implements MediaFileServiceInterface
{
    protected $writeArray = [];

    protected $uuids = [];

    /**
     * @var EntityRepositoryInterface
     */
    private $mediaFileRepo;

    public function __construct(EntityRepositoryInterface $mediaFileRepo)
    {
        $this->mediaFileRepo = $mediaFileRepo;
    }

    public function writeMediaFile(Context $context): void
    {
        $this->checkMediaIdsForDuplicates($context);

        if (empty($this->writeArray)) {
            return;
        }

        $this->mediaFileRepo->create($this->writeArray, $context);
        $this->writeArray = [];
        $this->uuids = [];
    }

    public function saveMediaFile(array $mediaFile): void
    {
        $mediaId = $mediaFile['mediaId'];
        if (isset($this->uuids[$mediaId])) {
            return;
        }

        $this->uuids[$mediaId] = $mediaId;
        $this->writeArray[] = $mediaFile;
    }

    public function setWrittenFlag(array $converted, MigrationContextInterface $migrationContext, Context $context): void
    {
        $dataSet = $migrationContext->getDataSet();

        $mediaUuids = [];
        foreach ($converted as $data) {
            if ($dataSet::getEntity() === MediaDefinition::getEntityName()) {
                $mediaUuids[] = $data['id'];
                continue;
            }

            if ($dataSet::getEntity() === ProductDefinition::getEntityName()) {
                if (!isset($data['media'])) {
                    continue;
                }

                foreach ($data['media'] as $media) {
                    if (!isset($media['media'])) {
                        continue;
                    }

                    $mediaUuids[] = $media['media']['id'];
                }
            }

            if ($dataSet::getEntity() === PropertyGroupOptionDefinition::getEntityName()) {
                if (!isset($data['media']['id'])) {
                    continue;
                }

                $mediaUuids[] = $data['media']['id'];
            }
        }

        if (empty($mediaUuids)) {
            return;
        }

        $this->saveWrittenFlag($mediaUuids, $migrationContext, $context);
    }

    private function checkMediaIdsForDuplicates(Context $context): void
    {
        if (empty($this->writeArray)) {
            return;
        }

        $runId = null;
        $files = [];
        $mediaIds = [];
        foreach ($this->writeArray as $mediaFile) {
            if ($runId === null) {
                $runId = $mediaFile['runId'];
            }

            $files[$mediaFile['mediaId']] = $mediaFile;
            $mediaIds[] = $mediaFile['mediaId'];
        }

        if (empty($mediaIds)) {
            return;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR,
            [
                new MultiFilter(MultiFilter::CONNECTION_AND,
                    [
                        new EqualsAnyFilter('mediaId', $mediaIds),
                        new EqualsFilter('written', true),
                        new EqualsFilter('processed', true),
                    ]
                ),

                new MultiFilter(MultiFilter::CONNECTION_AND,
                    [
                        new EqualsAnyFilter('mediaId', $mediaIds),
                        new EqualsFilter('runId', $runId),
                    ]
                ),
            ]
        ));
        $mediaFiles = $this->mediaFileRepo->search($criteria, $context);

        /** @var SwagMigrationMediaFileEntity $mediaFile */
        foreach ($mediaFiles->getElements() as $mediaFile) {
            unset($files[$mediaFile->getMediaId()]);
        }

        $this->writeArray = array_values($files);
    }

    private function saveWrittenFlag(array $mediaUuids, MigrationContextInterface $migrationContext, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('mediaId', $mediaUuids));
        $criteria->addFilter(new EqualsFilter('runId', $migrationContext->getRunUuid()));
        $mediaFiles = $this->mediaFileRepo->search($criteria, $context);

        $updateWrittenMediaFiles = [];
        foreach ($mediaFiles->getElements() as $data) {
            /* @var SwagMigrationMediaFileEntity $data */
            $value = $data->getId();
            $updateWrittenMediaFiles[] = [
                'id' => $value,
                'written' => true,
            ];
        }

        if (empty($updateWrittenMediaFiles)) {
            return;
        }

        $this->mediaFileRepo->update($updateWrittenMediaFiles, $context);
    }
}
