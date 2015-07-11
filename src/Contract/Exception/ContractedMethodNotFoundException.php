<?php

namespace Giritli\Contract\Exception;

class ContractedMethodNotFoundException extends ContractException
{
    
    public function __construct($class, $method)
    {
        parent::__construct(sprintf('Contracted method "%s" not found in parent class "%s".', $method, $class));
    }
}
