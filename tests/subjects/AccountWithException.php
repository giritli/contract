<?php

/**
 * Testing class which we will wrap with a contract
 */
class AccountWithException {

    public $balance = 0;

    public function deposit($amount) {
        $this->balance += (int) $amount;

        throw new OutOfBoundsException('This methods state is altered.');
    }

    public function getBalance() {
        return $this->balance;
    }
}