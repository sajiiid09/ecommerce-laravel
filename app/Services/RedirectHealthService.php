<?php

namespace App\Services;

use App\Models\Redirect;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RedirectHealthService
{
    public function validate(array $data, ?Redirect $ignore = null): void
    {
        $this->validateBatch([$data], $ignore);
    }

    public function validateBatch(array $rows, ?Redirect $ignore = null): void
    {
        $graph = Redirect::query()
            ->when($ignore?->id, fn ($query) => $query->where('id', '<>', $ignore->id))
            ->pluck('to_url', 'from_path')
            ->all();
        $seenRows = [];

        foreach ($rows as $row) {
            $from = $this->normalizePath((string) ($row['from_path'] ?? ''));
            $to = trim((string) ($row['to_url'] ?? ''));

            if ($from === '/' || $to === '') {
                throw new InvalidArgumentException('Redirect paths and destinations are required.');
            }

            if (! in_array((int) ($row['status_code'] ?? 0), [301, 302, 307, 308], true)) {
                throw new InvalidArgumentException('Redirect CSV contains an invalid status code.');
            }

            if (filter_var($row['enabled'] ?? null, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) === null) {
                throw new InvalidArgumentException('Redirect enabled values must be boolean.');
            }

            if (Str::startsWith(strtolower($to), ['javascript:', 'data:', 'vbscript:', 'file:'])) {
                throw new InvalidArgumentException('Unsafe redirect destination.');
            }

            if (isset($seenRows[$from])) {
                throw new InvalidArgumentException('Redirect CSV contains duplicate source paths.');
            }

            $seenRows[$from] = true;
            $graph[$from] = $to;
        }

        foreach (array_keys($seenRows) as $source) {
            $visited = [];
            $path = [];
            $current = $source;

            while (isset($graph[$current]) && $this->isInternalPath($graph[$current])) {
                if (isset($visited[$current])) {
                    throw new InvalidArgumentException('Redirect cycle detected.');
                }

                $visited[$current] = true;
                $path[] = $current;
                $current = $this->normalizePath($graph[$current]);
            }

            if (count($path) > 1) {
                throw new InvalidArgumentException('Redirect chains are not allowed; point the source directly to its final destination.');
            }
        }
    }

    public function normalizePath(string $path): string
    {
        return '/'.trim($path, '/');
    }

    private function isInternalPath(string $url): bool
    {
        return str_starts_with($url, '/') && ! str_starts_with($url, '//');
    }
}
