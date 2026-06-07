<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Resizes and compresses uploaded images before they hit storage, to slash
 * Supabase egress (every image view downloads the stored file). Conservative
 * defaults — quality stays visually clean while cutting size substantially.
 *
 * Animated GIFs and non-images are passed through untouched (return null) so
 * the caller stores the original.
 */
class ImageOptimizer
{
    /**
     * Cache-Control applied to stored images. Filenames are content-unique
     * (random), so the file at a given URL never changes — safe to cache for a
     * year as immutable. This lets browsers reuse images across page views
     * instead of re-downloading them, cutting storage egress dramatically.
     */
    public const CACHE_CONTROL = 'public, max-age=31536000, immutable';

    public function __construct(
        private readonly int $maxDimension = 1600,
        private readonly int $quality = 85,
    ) {}

    /**
     * Optimize an uploaded image.
     *
     * @return array{contents: string, extension: string, mime: string, width: int, height: int}|null
     *         null = caller should store the original file unchanged.
     */
    public function optimize(UploadedFile $file, ?int $maxDimension = null, ?int $quality = null): ?array
    {
        $mime = $file->getClientMimeType();

        // Only optimize raster images we can safely re-encode. Skip GIF to
        // preserve animation, and skip anything non-image (e.g. PDFs).
        if (! \in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        $maxDimension ??= $this->maxDimension;
        $quality ??= $this->quality;

        try {
            $image = (new ImageManager(new Driver()))->decodePath($file->getRealPath());

            // Fit within a maxDimension x maxDimension box, preserving aspect
            // ratio. Never upscales — a small image is left as-is.
            $image->scaleDown(width: $maxDimension, height: $maxDimension);

            [$encoded, $extension, $outMime] = match ($mime) {
                // PNGs are usually photos/illustrations stored losslessly (huge
                // files), sometimes graphics with transparency. Re-encode to
                // WebP at a conservative quality 90: massive size cut, stays
                // crisp for text/edges, and transparency is preserved natively
                // (no black-background surprise).
                'image/png'  => [$image->encode(new WebpEncoder(quality: 90)), 'webp', 'image/webp'],
                'image/webp' => [$image->encode(new WebpEncoder(quality: $quality)), 'webp', 'image/webp'],
                // JPEGs are already lossy photos — keep the format (no risky
                // second-generation re-encode) at a visually clean quality.
                default      => [$image->encode(new JpegEncoder(quality: $quality)), 'jpg', 'image/jpeg'],
            };

            return [
                'contents'  => (string) $encoded,
                'extension' => $extension,
                'mime'      => $outMime,
                'width'     => $image->width(),
                'height'    => $image->height(),
            ];
        } catch (\Throwable $e) {
            // Never let an optimization hiccup break an upload — fall back to
            // storing the original.
            Log::warning('Image optimization failed; storing original', [
                'mime'  => $mime,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
