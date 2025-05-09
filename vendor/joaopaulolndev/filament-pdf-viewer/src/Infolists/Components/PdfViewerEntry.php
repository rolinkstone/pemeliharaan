<?php

namespace Joaopaulolndev\FilamentPdfViewer\Infolists\Components;

use Closure;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\UnableToCheckFileExistence;
use Throwable;

class PdfViewerEntry extends ViewEntry
{
    protected string $view = 'filament-pdf-viewer::filament.components.infolists.pdf-viewer-entry';

    protected string $minHeight = '50svh';

    protected string|Closure $fileUrl = '';

    protected string|Closure|null $disk = null;

    protected string|Closure $visibility = 'public';

    protected bool|Closure $shouldCheckFileExistence = true;

    public function minHeight(string $minHeight): self
    {
        $this->minHeight = $minHeight;

        return $this;
    }

    public function getMinHeight(): string
    {
        return $this->minHeight;
    }

    public function getDisk(): Filesystem
    {
        return Storage::disk($this->getDiskName());
    }

    public function getDiskName(): string
    {
        return $this->evaluate($this->disk) ?? config('filament.default_filesystem_disk');
    }

    public function getFileUrl(?string $state = null): string
    {
        if (! $state) {
            return '';
        }

        if ((filter_var($state, FILTER_VALIDATE_URL) !== false) || str($state)->startsWith('data:')) {
            return $state;
        }

        /** @var FilesystemAdapter $storage */
        $storage = $this->getDisk();

        if ($this->shouldCheckFileExistence()) {
            try {
                if (! $storage->exists($state)) {
                    return null;
                }
            } catch (UnableToCheckFileExistence $exception) {
                return null;
            }
        }

        if ($this->getVisibility() === 'private') {
            try {
                return $storage->temporaryUrl(
                    $state,
                    now()->addMinutes(60),
                );
            } catch (Throwable $exception) {
                // This driver does not support creating temporary URLs.
            }
        }

        return $storage->url($state);
    }

    public function getVisibility(): string
    {
        return $this->evaluate($this->visibility);
    }

    public function checkFileExistence(bool|Closure $condition = true): static
    {
        $this->shouldCheckFileExistence = $condition;

        return $this;
    }

    public function shouldCheckFileExistence(): bool
    {
        return (bool) $this->evaluate($this->shouldCheckFileExistence);
    }

    /**
     * @return null|string|void
     */
    public function getRoute(string $file)
    {
        return $this->getFileUrl($file);
    }
}
