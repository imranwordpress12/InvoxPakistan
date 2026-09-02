<?php

namespace App\Domain\Invoices\BulkUpload;

use RuntimeException;

/**
 * The uploaded file itself can't be processed at all (empty, unreadable,
 * or missing required columns) — distinct from a single row/invoice being
 * rejected, which is reported in `BulkUploadResult::$errors` instead so
 * the rest of the file can still be imported.
 */
class InvalidBulkUploadFileException extends RuntimeException {}
