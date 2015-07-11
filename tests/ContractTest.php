<?php


// Require test classes
require_once 'subjects/Account.php';
require_once 'subjects/AccountContract.php';
require_once 'subjects/AccountContractNoParent.php';

class ContractTest extends PHPUnit_Framework_TestCase {

	
	/**
	 * Store non contracted class.
	 * 
	 * @var Account
	 */
	protected $account;
	
	
	/**
	 * Store contracted class.
	 * 
	 * @var AccountContract
	 */
	protected $accountContract;
	
	
	/**
	 * Test deposit amount.
	 * 
	 * @var integer
	 */
	protected $amount;
	
	
	/**
	 * Build contracted and non contracted classes.
	 */
	protected function setUp() {
		$this->amount = 3;
		$this->account = new Account;
		$this->accountContract = new AccountContract;
	}
	
	
	/**
	 * Test that both classes are an instance of non contracted class.
	 */
	public function testContractType() {
		$this->assertTrue($this->account instanceof Account);
		$this->assertTrue($this->accountContract instanceof Account);
		$this->assertTrue($this->accountContract instanceof AccountContract);
	}
	
	
	/**
	 * Test that depositing the same amount on contracted and non contracted
	 * class yields the same result.
	 */
	public function testDepositMoney() {
		$this->account->deposit($this->amount);
		$this->accountContract->deposit($this->amount);
		$this->assertEquals($this->amount, $this->account->getBalance());
		$this->assertEquals($this->account->getBalance(), $this->accountContract->getBalance());
	}
	
	
	/**
	 * @expectedException \Giritli\Contract\Exception\RequireContractFailedException
	 */
	public function testRequireContractWithNonNumericValue() {
		$this->accountContract->deposit('abc');
	}
	
	
	/**
	 * @expectedException \Giritli\Contract\Exception\RequireContractFailedException
	 */
	public function testRequreContractWithNegativeValue() {
		$this->accountContract->deposit(-3);
	}
	
	
	/**
	 * Having a balance of 6, in increments of 4 and 2, mean that the balance
	 * is never 5 and therefore this test should pass.
	 */
	public function testEnsureContractWithValidValue() {
		$this->accountContract
			->deposit(4)
			->deposit(2);
		
		$this->assertEquals(6, $this->accountContract->getBalance());
	}
	
	
	/**
	 * The ensure contract on AccountContract specifies that the balance of the
	 * account must never be 5.
	 * 
	 * @expectedException \Giritli\Contract\Exception\EnsureContractFailedException
	 */
	public function testEnsureContractWithBreakingValue() {
		
		
		// Having a total balance of 5 breaks an ensure contract
		$this->accountContract
			->deposit(3)
			->deposit(2);
	}
	
	
	/**
	 * Depositing too much money into the account will break the second
	 * ensure contract.
	 * 
	 * @depends testDepositMoney
	 * @expectedException \Giritli\Contract\Exception\EnsureContractFailedException
	 */
	public function testEnsureContractWithTooMuchMoney() {
		
		$this->accountContract->deposit(70);
	}
	
	
	/**
	 * After depositing too much money, make sure that the contracted class
	 * reverts to its previous state.
	 * 
	 * @depends testEnsureContractWithTooMuchMoney
	 */
	public function testDepositAmountReverted() {
		
		$this->accountContract->deposit($this->amount);
		
		try {
			$this->accountContract->deposit(70);
		} catch (\Giritli\Contract\Exception\EnsureContractFailedException $ex) {
			// Ignore
		}
		
		$this->assertEquals($this->amount, $this->accountContract->getBalance());
	}


	/**
	 * @expectedException \Giritli\Contract\Exception\ParentClassNotFoundException
	 */
	public function testParentClassExists() {
		$account = new AccountContractNoParent();
		$account->deposit(1);
	}


	/**
	 * @expectedException \Giritli\Contract\Exception\ContractedMethodNotFoundException
	 */
	public function testMethodNotFound() {
		$this->accountContract->parentMethodNotDefined();
	}
}