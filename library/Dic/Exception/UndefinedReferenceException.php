<?php
namespace Pincrowd\Di\Exception;

use Pincrowd\Di\Exception,
    DomainException;

class UndefinedReferenceException extends DomainException implements Exception
{
}
