<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class StorageUrlExtension extends AbstractExtension
{
    public function __construct(
        private readonly Packages $packages,
        private readonly UrlGeneratorInterface $urlGenerator,
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('storage_url', [$this, 'storageUrl']),
        ];
    }

    public function storageUrl(?string $reference, string $legacyPrefix = ''): ?string
    {
        $reference = trim((string) $reference);
        if ($reference === '') {
            return null;
        }

        if (str_starts_with($reference, 'supabase:')) {
            if (trim(substr($reference, strlen('supabase:'))) === '') {
                return null;
            }

            return $this->urlGenerator->generate('app_supabase_storage_proxy', [
                'reference' => $reference,
            ]);
        }

        if (filter_var($reference, FILTER_VALIDATE_URL) || str_starts_with($reference, '/')) {
            return $reference;
        }

        $path = trim($legacyPrefix, '/');
        if ($path !== '') {
            $path .= '/';
        }

        return $this->packages->getUrl($path . ltrim($reference, '/'));
    }
}
