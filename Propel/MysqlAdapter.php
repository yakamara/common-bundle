<?php declare(strict_types=1);

namespace Yakamara\CommonBundle\Propel;

use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\Map\ColumnMap;
use Propel\Runtime\Util\PropelDateTime;

class MysqlAdapter extends \Propel\Runtime\Adapter\Pdo\MysqlAdapter
{
    public function formatTemporalValue($value, ColumnMap $cMap)
    {
        /** @var \DateTime|\DateTimeImmutable $dt */
        $dt = PropelDateTime::newInstance($value);

        if ($dt) {
            switch ($cMap->getType()) {
                case PropelTypes::TIMESTAMP:
                case PropelTypes::BU_TIMESTAMP:
                    $dt = clone $dt;
                    $dt = $dt->setTimezone(new \DateTimeZone('UTC'));

                    $value = $dt->format($this->getTimestampFormatter());
                    break;
                case PropelTypes::DATE:
                case PropelTypes::BU_DATE:
                    $value = $dt->format($this->getDateFormatter());
                    break;
                case PropelTypes::TIME:
                    $dt = clone $dt;
                    $dt = $dt->setTimezone(new \DateTimeZone('UTC'));

                    $value = $dt->format($this->getTimeFormatter());
                    break;
            }
        }

        return $value;
    }
}
