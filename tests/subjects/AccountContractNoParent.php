<?php

/**
 * Contract class extends the initial class, while implementing
 * Contract interface. ContractTrait implements all Contract interface
 * methods.
 */
class AccountContractNoParent implements \Giritli\Contract\Contract {

    use \Giritli\Contract\ContractTrait;

    public function deposit($amount) {


        $this->requires(function($amount) {

            return is_numeric($amount) && $amount >= 0;
        }, 'Amount must be a number and non negative.');

        return $this->enforce();
    }
}