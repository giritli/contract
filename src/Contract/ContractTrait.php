<?php

namespace Giritli\Contract;

use Closure;
use Giritli\Contract\Exception\ContractedMethodNotFoundException;
use Giritli\Contract\Exception\EnsureContractFailedException;
use Giritli\Contract\Exception\ParentClassNotFoundException;
use Giritli\Contract\Exception\RequireContractFailedException;

trait ContractTrait
{
    
    
    /**
     * Multi-dimensional array of contracts.
     *
     * @var array
     */
    protected $contracts = [];
    
    
    /**
     * {@inheritdoc}
     */
    public function requires(Closure $callback, $message = null, $ensures = false)
    {
        
        
        /**
         * Trace calls to get initial call to contracted method. Increase
         * backtrace limit by 1 if called through ensures method.
         */
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2 + !!$ensures);
        $stack = (object) array_pop($trace);

        
        // Build signature for called contract class
        $signature = $stack->class . '::' . $stack->function;

        if (!isset($this->contracts[$signature])) {
            $this->contracts[$signature] = (object) [
                'requires' => [],
                'ensures' => [],
                'enforced' => false
            ];
        }
        
        if ($this->contracts[$signature]->enforced) {
            return $this;
        }
        
        $parent = get_parent_class($this);
        
        if (!$parent) {
            throw new ParentClassNotFoundException(get_called_class());
        }
        
        if (!method_exists($parent, $stack->function)) {
            throw new ContractedMethodNotFoundException($parent, $stack->function);
        }
        
        
        // Add callback to contracts array by signature
        $this->contracts[$signature]->{$ensures ? 'ensures' : 'requires'}[] = (object) [
            'callback' => $callback,
            'message' => $message
        ];

        return $this;
    }

    public function ensures(Closure $callback, $message = null)
    {

        
        // Calls requires method to save on code duplication
        $this->requires($callback, $message, true);

        return $this;
    }

    public function enforce($isClone = false)
    {


        // Get initial method call with arguments
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2 + !!$isClone);
        $stack = (object) array_pop($trace);
        
        
        /**
         * If object is not cloned, clone and enforce on clone, this part
         * only continues if enforce does not throw an exception
         */
        if (!$isClone) {
            $clonedObject = clone $this;
            $clonedObject->enforce(true);
        }

        $signature = $stack->class . '::' . $stack->function;
        $contracts = false;

        if (isset($this->contracts[$signature])) {
            $contracts = &$this->contracts[$stack->class . '::' . $stack->function];
            $contracts->enforced = true;
            
            
            // Loop through requires contracts and execute every one
            foreach ($contracts->requires as $requires) {
                if (!call_user_func_array($requires->callback, $stack->args)) {
                    throw new RequireContractFailedException(
                        $requires->message ?: 'Contract requirements unenforceable.'
                    );
                }
            }
        }
        
        
        // Get result of contracted method
        $result = call_user_func_array(['parent', $stack->function], $stack->args);
        
        
        // If no contracts exist, unlikely, return result
        if (!$contracts) {
            return $result;
        }
                
        foreach ($contracts->ensures as $ensures) {
            $callback = Closure::bind($ensures->callback, $this);
            
            if (!$callback($result)) {
                throw new EnsureContractFailedException($ensures->message ?: 'Contract could not be enforced.');
            }
        }

        return $result;
    }
}
