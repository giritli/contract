<?php

namespace Giritli\Contract;

use Giritli\Contract\Exception;

interface Contract
{

    
    /**
     * Closure contract which receives all parameters passed to the method.
     *
     * @param \Closure $callback
     * @param string $message Exception message to throw if contract fails
     * @param boolean $ensures Used only when called in ensures method
     *
     * @throws Exception\ParentClassNotFoundException
     * @throws Exception\ContractedMethodNotFoundException
     *
     * @return $this
     */
    public function requires(\Closure $callback, $message = null, $ensures = false);

    
    /**
     * Closure contract which receives method result and bound to current instance.
     *
     * @param \Closure $callback
     * @param string $message Exception message to throw if contract fails
     *
     * @return $this
     */
    public function ensures(\Closure $callback, $message = null);

    
    /**
     * Enforce all contracts and return result if contracts pass.
     *
     * @param boolean $isClone
     *
     * @throws Exception\RequireContractFailedException If require contract fails
     * @throws Exception\EnsureContractFailedException if ensure contract fails
     *
     * @return mixed Value of original method return
     */
    public function enforce($isClone = false);
}
