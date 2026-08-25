<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Redirect;
use App\Models\RedirectHit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class RedirectService
{
    public function __construct(
        private readonly RedirectHealthService $health,
        private readonly ContentCache $cache,
    ) {}

    public function save(array $data, ?Redirect $redirect = null): Redirect
    {
        $redirect ??= new Redirect;
        $normalized = [
            ...$data,
            'from_path' => $this->health->normalizePath((string) ($data['from_path'] ?? '')),
            'to_url' => trim((string) ($data['to_url'] ?? '')),
            'status_code' => (int) ($data['status_code'] ?? 301),
            'enabled' => filter_var($data['enabled'] ?? true, FILTER_VALIDATE_BOOL),
        ];
        $this->health->validate($normalized, $redirect);
        $previousPath = $redirect->from_path;
        $redirect->fill($normalized);
        $redirect->forceFill(['updated_by' => auth()->id()]);
        $redirect->save();

        $this->cache->forget(array_filter([
            $previousPath ? $this->cache->redirect($previousPath) : null,
            $this->cache->redirect($redirect->from_path),
        ]));

        return $redirect;
    }

    public function resolve(string $path): ?Redirect
    {
        $normalizedPath = $this->health->normalizePath($path);
        $cacheKey = $this->cache->redirect($normalizedPath);

        if (cache()->has($cacheKey)) {
            $cached = cache()->get($cacheKey);

            if ($cached instanceof Redirect || $cached === null) {
                return $cached;
            }

            cache()->forget($cacheKey);
        }

        return cache()->remember($cacheKey, 300, fn (): ?Redirect => Redirect::enabled()->where('from_path', $normalizedPath)->first());
    }

    public function recordHit(Redirect $redirect): void
    {
        $redirect->increment('hit_count');
        $redirect->update(['last_hit_at' => now()]);
        RedirectHit::create([
            'redirect_id' => $redirect->id,
            'path' => $redirect->from_path,
            'ip_hash' => request()->ip() ? hash('sha256', request()->ip()) : null,
        ]);
    }

    public function destination(string $path): ?array
    {
        if (! $redirect = $this->resolve($path)) {
            return null;
        }

        $this->recordHit($redirect);

        return ['url' => $redirect->to_url, 'status' => $redirect->status_code];
    }

    public function import(UploadedFile $file): int
    {
        $handle = fopen($file->getRealPath(), 'rb');
        $headers = array_map(fn ($value): string => strtolower(trim((string) $value)), fgetcsv($handle) ?: []);
        $required = ['from_path', 'to_url', 'status_code', 'enabled'];

        if (array_diff($required, $headers) || count($headers) !== count(array_unique($headers))) {
            throw new InvalidArgumentException('Redirect CSV must contain unique from_path, to_url, status_code and enabled headers.');
        }

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($headers)) {
                throw new InvalidArgumentException('Redirect CSV contains a row with an invalid number of columns.');
            }

            $rows[] = array_combine($headers, $row);
        }

        fclose($handle);
        $this->health->validateBatch($rows);

        DB::transaction(function () use ($rows): void {
            foreach ($rows as $row) {
                $this->save($row);
            }
        });

        return count($rows);
    }

    public function export(): string
    {
        $path = 'exports/redirects-'.now()->format('YmdHis').'.csv';
        $stream = fopen('php://temp', 'w+');
        fputcsv($stream, ['from_path', 'to_url', 'status_code', 'enabled', 'hit_count', 'last_hit_at']);
        Redirect::query()->orderBy('id')->each(fn (Redirect $redirect) => fputcsv($stream, [
            $redirect->from_path,
            $redirect->to_url,
            $redirect->status_code,
            $redirect->enabled ? '1' : '0',
            $redirect->hit_count,
            $redirect->last_hit_at?->toIso8601String(),
        ]));
        rewind($stream);
        Storage::disk('local')->put($path, stream_get_contents($stream));
        fclose($stream);

        return $path;
    }

    public function health(iterable $redirects): array
    {
        $issues = [];
        $enabled = Redirect::enabled()->pluck('to_url', 'from_path')->all();

        foreach ($redirects as $redirect) {
            if (! $redirect->enabled) {
                $issues[$redirect->id][] = 'Disabled';

                continue;
            }

            if (! $this->isInternalPath($redirect->to_url)) {
                continue;
            }

            $destination = $this->normalizeDestination($redirect->to_url);
            $visited = [];
            $current = $redirect->from_path;
            $depth = 0;

            while (isset($enabled[$current]) && $this->isInternalPath($enabled[$current])) {
                if (isset($visited[$current])) {
                    $issues[$redirect->id][] = 'Redirect loop';

                    break;
                }

                $visited[$current] = true;
                $current = $this->normalizeDestination($enabled[$current]);
                $depth++;
            }

            if ($depth > 0) {
                $issues[$redirect->id][] = 'Redirect chain';
            }

            if (! isset($enabled[$destination]) && ! $this->knownDestination($destination)) {
                $issues[$redirect->id][] = 'Broken destination';
            }
        }

        return $issues;
    }

    private function isInternalPath(string $url): bool
    {
        return str_starts_with($url, '/') && ! str_starts_with($url, '//');
    }

    private function normalizeDestination(string $url): string
    {
        return $this->health->normalizePath((string) parse_url($url, PHP_URL_PATH));
    }

    private function knownDestination(string $path): bool
    {
        return $path === '/'
            || $path === '/offers'
            || str_starts_with($path, '/category/')
            || str_starts_with($path, '/product/')
            || Page::published()->where('slug', trim($path, '/'))->exists();
    }
}
