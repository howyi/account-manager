<?php
namespace App\Enums;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class AbstractEnum extends Type
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $values = array_map(function ($val) {
            return "'".$val."'";
        }, $this->getValues());

        return "ENUM(".implode(", ", $values).")";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, $this->getValues())) {
            throw new \InvalidArgumentException("Invalid '".$this->getName()."' value.");
        }
        return $value;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    /**
     * @return array
     */
    abstract public function getValues(): array;
}
