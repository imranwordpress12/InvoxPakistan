<?php

namespace App\Domain\Transactions\Exceptions;

use RuntimeException;

/**
 * Thrown when an action requires a transaction to still be pending but it
 * no longer is — most commonly a double "Mark as Paid" submission
 * (PRD #64 case 6: "double mark paid must fail safely"). Callers catch this
 * and turn it into a normal flashed error rather than a 500.
 */
class TransactionAlreadyProcessedException extends RuntimeException {}
