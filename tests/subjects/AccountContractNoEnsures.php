<?php

/**
 * Contract class extends the initial class, while implementing
 * Contract interface. ContractTrait implements all Contract interface
 * methods.
 */
class AccountContractNoEnsures extends Account implements \Giritli\Contract\Contract {

    use \Giritli\Contract\ContractTrait;


    /**
     * Wrapper for Account::deposit.
     */
    public function depositReturningBalance($amount) {


        /**
         * Create requires contract, closure passed all arguments from
         * method.
         *
         * Requires contract is run before parameters are passed to
         * the method. This is to run checks against passed parameters.
         */
        $this->requires(function($amount) {


            // Enforce contract on amount parameter
            return is_numeric($amount) && $amount >= 0;
        }, 'Amount must be a number and non negative.');


        // Enforce all assigned contracts
        return $this->enforce();
    }
}