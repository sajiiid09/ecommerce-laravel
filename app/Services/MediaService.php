<?php

namespace App\Services;

use App\Models\MediaAsset;
use App\Models\MediaFolder;
use App\Models\MediaUsage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MediaService
{
    public function upload(UploadedFile $file, string $folder = 'general', ?int $folderId = null): MediaAsset
    {
        $folderId ??= MediaFolder::where('slug', $folder)->value('id');
        $path = $file->store('media/'.$folder.'/'.now()->format('Y/m'), 'public');
        $dimensions = @getimagesize($file->getRealPath()) ?: [];

        $asset = MediaAsset::create([
            'folder_id' => $folderId,
            'disk' => 'public',
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'original_filename' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'checksum' => hash_file('sha256', $file->getRealPath()),
        ]);

        $asset->forceFill(['uploaded_by' => auth()->id()])->save();

        return $asset;
    }

    public function attach(MediaAsset $asset, object $model, ?string $role = null): MediaUsage
    {
        return MediaUsage::updateOrCreate([
            'media_asset_id' => $asset->id,
            'usable_type' => $model::class,
            'usable_id' => $model->getKey(),
            'role' => $role,
        ]);
    }

    public function detach(MediaAsset $asset, object $model, ?string $role = null): void
    {
        $asset->usages()->where('usable_type', $model::class)->where('usable_id', $model->getKey())
            ->when($role, fn ($query) => $query->where('role', $role))->delete();
    }

    public function delete(MediaAsset $asset): void
    {
        if ($asset->usages()->exists()) {
            throw new RuntimeException('This media asset is still in use and cannot be deleted.');
        }

        // Keep the stored file for recovery. The asset can be permanently purged later.
        $asset->delete();
    }

    public function purge(MediaAsset $asset): void
    {
        if ($asset->usages()->exists()) {
            throw new RuntimeException('Remove all media usages before purging this asset.');
        }

        Storage::disk($asset->disk)->delete($asset->path);
        $asset->forceDelete();
    }

    public function usageSummary(MediaAsset $asset): array
    {
        return $asset->usages()->with('usable')->get()->map(fn (MediaUsage $usage) => [
            'type' => class_basename($usage->usable_type),
            'id' => $usage->usable_id,
            'role' => $usage->role,
        ])->all();
    }

    public function updateMetadata(MediaAsset $asset, array $data): MediaAsset
    {
        $asset->fill(collect($data)->only(['alt_text', 'title', 'caption', 'folder_id'])->all());
        $asset->save();

        return $asset->fresh();
    }

    public function createFolder(string $name, ?int $parentId = null): MediaFolder
    {
        $folder = MediaFolder::firstOrCreate(
            ['parent_id' => $parentId, 'slug' => Str::slug($name)],
            ['name' => $name],
        );
        $folder->forceFill(['created_by' => $folder->created_by ?? auth()->id(), 'updated_by' => auth()->id()])->save();

        return $folder;
    }
}
