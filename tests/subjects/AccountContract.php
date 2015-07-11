<?php

/**
 * Contract class extends the initial class, while implementing
 * Contract interface. ContractTrait implements all Contract interface
 * methods.
 */
class AccountContract extends Account implements \Giritli\Contract\Contract {

	use \Giritli\Contract\ContractTrait;

	
	/**
	 * Wrapper for Account::deposit.
	 */
	public function deposit($amount) {

		
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

		
		/**
		 * Create ensures contract, closure passed result from method and
		 * bound to method's object.
		 * 
		 * Ensures contract is run after method and is bound to method object
		 * to check object state.
		 */
		$this->ensures(function($result) {
			
			
			// Check if balance is not 5.
			return $this->balance !== 5;
		}, 'Account cannot have a balance of 5.');
		
		
		/**
		 * Same as the ensure contract except states that balance must 
		 * not exceed 50.
		 */
		$this->ensures(function($result) {
			return $this->balance <= 50;
		}, 'You have too much money. Consider donating to charity.');
		

		// Enforce all assigned contracts
		return $this->enforce();
	}


	public function parentMethodNotDefined() {
		$this->ensures(function() {
			return true;
		});
		return $this->enforce();
	}
}