<?php

/**
 * Testing class which we will wrap with a contract
 */
class AccountWithExceptionContract extends AccountWithException implements \Giritli\Contract\Contract {

    use \Giritli\Contract\ContractTrait;

    public function deposit($amount) {

        $this->requires(function($amount) {

            return is_int($amount);
        }, 'Amount must be an integer.');

        $this->ensures(function($value) {

            return $this->balance <= 10;
        }, 'Balance must be 10 or less.');

        return $this->enforce();
    }
}