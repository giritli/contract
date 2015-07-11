[![Build Status](https://travis-ci.org/giritli/contract.svg)](https://travis-ci.org/giritli/contract)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/giritli/contract/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/giritli/contract/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/giritli/contract/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/giritli/contract/?branch=master)

# Contract
Contract is a PHP implementation of the design by contract pattern. It allows you to wrap a contract around a pre-existing class and intercept the requirements and outcome of result and class state. The contracted class also reverts state if the contract is breached.

## Example usage
Create your initial class that will be wrapped with a contract.

    // Create your normal class
    class Account {
        public $balance = 0;
        public function deposit($amount) {
            $this->balance += (int) $amount;
        }
        public function getBalance() {
            return $this->balance;
        }
    }

For a class to be a contract, it must extend the class to wrap, and implement `\Giritli\Contract\Contract`. The implementation of the interface is included as the trait: `\Giritli\Contract\ContractTrait`.

To make a contract on a method, define a method which also exists in the parent class. Using an interface can make this easier. The contract interface provides 3 methods:
  - `requires(\Closure $callback, $message = null)`
      - Any closure passed to this method will have all the method parameters passed to it. It should have the same parameter definitions as the parent method.
      - The closure must return a true or false if the contract on the parameters passes.
      - The message is what will be thrown as an exception message if the contract fails.
      - A good use for this would be parameter validation.
  - `ensures(\Closure $callback, $message = null)`
      - Any closure passed to this method will have the result of the initial parent method which is returned. The closure is also bound to the object so that the state of the object can also be checked with the `$this` variable.
      - The message is what will be thrown as an exception message if the contract fails.
  - `enforce()`
      - This method specifies at which point the parent method gets called. All contracts must be defined before `enforce()` is called.

Example usage:

    // Create the contract class
    class AccountContract extends Account implements \Giritli\Contract\Contract {
        use \Giritli\Contract\ContractTrait;
        public function deposit($amount) {
        
            $this->requires(function($amount) {
                return is_numeric($amount) && $amount >= 0;
            }, 'Amount must be a number and non negative.');
    
            $this->ensures(function($result) {
                return $this->balance !== 5;
            }, 'Account cannot have a balance of 5.');
        
            return $this->enforce();
        }
    }

Once you have created your class and contract, you can use it just like a regular class:

    $account = new AccountContract();
    
    try {
        $account->deposit(4);
    } catch (\Giritli\Contract\Exception\ContractFailedException $e) {
    }
    
    $account->getBalance(); // 4
    
    /**
     * AccountContract is always instance of Account,
     * conforms to Liskov substitution principle
     */
    $account instanceof Account; // true

If a contract fails, the object is reverted to its last good state:

    $account = new AccountContract();
    
    try {
        $account->deposit(3);
        $account->deposit(2); // Will fail ensures contract as balance is now 5
    } catch (\Giritli\Contract\Exception\EnsureContractFailedException $e) {
        echo $e->getMessage(); // Account cannot have a balance of 5.
    }
    
    $account->getBalance(); // 3


## How does it work?
The contract trait is deceptively simple. When you define a contracted method with a `require()` or `ensure()`, and `enforce()`, it traces the call stack to get the parameters passed to the initial method (which are intended for the parent method). These parameters are then passed to the `require` contracts and executed. If any contracts fail, an exception is thrown and the method does not execute. 

Once the `require` contracts have been executed, the class then clones itself and calls the `enforce` method on the clone. This method runs the parent method then runs the `ensure` contracts. If any `ensure` contracts fail, an exception is thrown and the method exits. If no exception is thrown, the call to the cloned object finishes and continues to the non cloned `ensure` method which then applies all changes to the active object.
