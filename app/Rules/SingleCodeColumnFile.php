<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class SingleCodeColumnFile implements Rule
{
    protected string $message = 'Invalid file format.';

    public function passes($attribute, $value): bool
    {
        try {
            $rows = Excel::toArray([], $value);

            if (empty($rows) || empty($rows[0]) || empty($rows[0][0])) {
                $this->message = 'File is empty.';
                return false;
            }

            $header = array_map('trim', $rows[0][0]);

            // Must have exactly ONE column
            if (count($header) !== 1) {
                $this->message = 'File must contain only one column named "code".';
                return false;
            }

            // Header must be exactly "code"
            if (strtolower($header[0]) !== 'code') {
                $this->message = 'File column name must be "code".';
                return false;
            }

            return true;

        } catch (\Throwable $e) {
            $this->message = 'Invalid CSV/XLSX file.';
            return false;
        }
    }

    public function message(): string
    {
        return $this->message;
    }
}
