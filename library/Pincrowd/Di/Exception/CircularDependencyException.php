<?php
namespace Pincrowd\Di\Exception;

use Pincrowd\Di\Exception,
    DomainException;

class CircularDependencyException extends DomainException implements Exception
{
}
