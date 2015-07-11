<?php

/**
 * Testing class which we will wrap with a contract
 */
class Account {

	public $balance = 0;

	public function deposit($amount) {
		$this->balance += (int) $amount;
		
		return $this;
	}

	public function getBalance() {
		return $this->balance;
	}
}