<?php

namespace App\Imports;

use App\Events\RowCreated;
use App\Models\Row;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class RowsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    protected $file;
    protected $errors = [];

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function model(array $row)
    {
        $lineNumber = $row['__line_number__']; // Assuming you add line number in the import
        $errors = $this->validateRow($row);

        if (!empty($errors)) {
            $this->errors[] = "$lineNumber - " . implode(', ', $errors);
            return null; // Skip this row
        }

        // Check for duplicates
        if (Row::where('id', $row['id'])->exists()) {
            $this->errors[] = "$lineNumber - Duplicate ID";
            return null; // Skip this row
        }

        $newRow = new Row([
            'id' => $row['id'],
            'name' => $row['name'],
            'date' => \Carbon\Carbon::createFromFormat('d.m.Y', $row['date']),
        ]);

        $newRow->save();

        // Отправка события
        event(new RowCreated($newRow));

        return $newRow;
    }

    public function batchSize(): int
    {
        return 1000; // Process 1000 rows at a time
    }

    public function chunkSize(): int
    {
        return 1000; // Read 1000 rows at a time
    }

    public function onStart()
    {
        Redis::set('import_progress', 0);
    }

    public function onEnd()
    {
        // Log errors to a file
        if (!empty($this->errors)) {
            file_put_contents('result.txt', implode(PHP_EOL, $this->errors));
        }
    }

    protected function validateRow($row)
    {
        $errors = [];

        if (!is_numeric($row['id']) || $row['id'] < 0) {
            $errors[] = 'ID must be an unsigned big integer';
        }

        if (!preg_match('/^[a-zA-Z\s]+$/', $row['name'])) {
            $errors[] = 'Name must contain only letters and spaces';
        }

        $date = \DateTime::createFromFormat('d.m.Y', $row['date']);
        if (!$date || $date->format('d.m.Y') !== $row['date']) {
            $errors[] = 'Date must be in d.m.Y format and must exist';
        }

        return $errors;
    }
}
