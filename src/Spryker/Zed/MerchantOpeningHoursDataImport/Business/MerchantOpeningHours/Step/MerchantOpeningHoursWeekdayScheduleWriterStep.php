<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\MerchantOpeningHoursDataImport\Business\MerchantOpeningHours\Step;

use Orm\Zed\MerchantOpeningHours\Persistence\SpyMerchantOpeningHoursWeekdayScheduleQuery;
use Spryker\Zed\DataImport\Business\Exception\InvalidDataException;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\PublishAwareStep;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use Spryker\Zed\MerchantOpeningHoursDataImport\Business\MerchantOpeningHours\DataSet\MerchantOpeningHoursWeekdayScheduleDataSetInterface;

class MerchantOpeningHoursWeekdayScheduleWriterStep extends PublishAwareStep implements DataImportStepInterface
{
    /**
     * @var array
     */
    protected const REQUIRED_DATA_SET_KEYS = [
        MerchantOpeningHoursWeekdayScheduleDataSetInterface::FK_MERCHANT,
        MerchantOpeningHoursWeekdayScheduleDataSetInterface::FK_WEEKDAY_SCHEDULE,
    ];

    /**
     * @uses \Spryker\Shared\MerchantOpeningHoursStorage\MerchantOpeningHoursStorageConfig::MERCHANT_OPENING_HOURS_PUBLISH
     *
     * @var string
     */
    protected const MERCHANT_OPENING_HOURS_PUBLISH = 'MerchantOpeningHours.merchant_opening_hours_schedule.publish';

    /**
     * @param \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface $dataSet
     *
     * @return void
     */
    public function execute(DataSetInterface $dataSet): void
    {
        $this->validateDataSet($dataSet);

        $merchantOpeningHoursWeekdayScheduleEntity = $this->createMerchantOpeningHoursWeekdaySchedulePropelQuery()
            ->filterByFkMerchant($dataSet[MerchantOpeningHoursWeekdayScheduleDataSetInterface::FK_MERCHANT])
            ->filterByFkWeekdaySchedule($dataSet[MerchantOpeningHoursWeekdayScheduleDataSetInterface::FK_WEEKDAY_SCHEDULE])
            ->findOneOrCreate();

        $merchantOpeningHoursWeekdayScheduleEntity
            ->setFkMerchant($dataSet[MerchantOpeningHoursWeekdayScheduleDataSetInterface::FK_MERCHANT])
            ->setFkWeekdaySchedule($dataSet[MerchantOpeningHoursWeekdayScheduleDataSetInterface::FK_WEEKDAY_SCHEDULE]);

        if ($merchantOpeningHoursWeekdayScheduleEntity->isNew() || $merchantOpeningHoursWeekdayScheduleEntity->isModified()) {
            $merchantOpeningHoursWeekdayScheduleEntity->save();

            $this->addPublishEvents(
                static::MERCHANT_OPENING_HOURS_PUBLISH,
                $merchantOpeningHoursWeekdayScheduleEntity->getFkMerchant(),
            );
        }
    }

    /**
     * @param \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface $dataSet
     *
     * @return void
     */
    protected function validateDataSet(DataSetInterface $dataSet): void
    {
        foreach (static::REQUIRED_DATA_SET_KEYS as $requiredDataSetKey) {
            $this->validateRequireDataSetByKey($dataSet, $requiredDataSetKey);
        }
    }

    /**
     * @param \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface $dataSet
     * @param string $requiredDataSetKey
     *
     * @throws \Spryker\Zed\DataImport\Business\Exception\InvalidDataException
     *
     * @return void
     */
    protected function validateRequireDataSetByKey(DataSetInterface $dataSet, string $requiredDataSetKey): void
    {
        if (!$dataSet[$requiredDataSetKey]) {
            throw new InvalidDataException(sprintf('"%s" is required.', $requiredDataSetKey));
        }
    }

    /**
     * @return \Orm\Zed\MerchantOpeningHours\Persistence\SpyMerchantOpeningHoursWeekdayScheduleQuery
     */
    protected function createMerchantOpeningHoursWeekdaySchedulePropelQuery(): SpyMerchantOpeningHoursWeekdayScheduleQuery
    {
        return SpyMerchantOpeningHoursWeekdayScheduleQuery::create();
    }
}
