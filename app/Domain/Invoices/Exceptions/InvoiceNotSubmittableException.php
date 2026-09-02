<?php

namespace App\Domain\Invoices\Exceptions;

use RuntimeException;

/**
 * Thrown when a submission is attempted on an invoice that isn't eligible
 * — already successful, or already mid-flight (PRD #8: a submitted
 * invoice cannot be edited/reversed/cancelled, which implies it can't be
 * re-submitted either while it's still in flight).
 */
class InvoiceNotSubmittableException extends RuntimeException {}
