<?php

namespace Giritli\Contract\Exception;

class ParentClassNotFoundException extends ContractException
{
    
    public function __construct($class)
    {
        parent::__construct(sprintf('Parent class for "%s" was not found.', $class));
    }
}
