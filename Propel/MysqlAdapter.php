<?php declare(strict_types=1);

namespace Yakamara\CommonBundle\Propel;

use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\Map\ColumnMap;
use Yakamara\DateTime\Date;
use Yakamara\DateTime\DateTime;

class MysqlAdapter extends \Propel\Runtime\Adapter\Pdo\MysqlAdapter
{
    public function formatTemporalValue($value, ColumnMap $cMap)
    {
        if (empty($value)) {
            return null;
        }

        switch ($cMap->getType()) {
            case PropelTypes::TIMESTAMP:
            case PropelTypes::BU_TIMESTAMP:
                $dateTime = DateTime::createFromUnknown($value);
                $value = $dateTime->toUtc()->formatIso();
                break;
            case PropelTypes::DATE:
            case PropelTypes::BU_DATE:
                $date = Date::createFromUnknown($value);
                $value = $date->formatIso();
                break;
            case PropelTypes::TIME:
                $dateTime = DateTime::createFromUnknown($value);
                $value = $dateTime->toUtc()->formatIsoTime();
                break;
        }

        return $value;
    }
}
