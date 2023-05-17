<?php

namespace App\Serializer\Normalizer;

use App\Entity\Order;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OrderNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Order $object */
        $assets = $object->getAsset();
        $asset = [
            'tokenId' => $assets->getTokenId(),
            'collection' => [
                'address' => $assets->getCollection()->getAddress(),
            ],
        ];

        return [
            'seller' => $object->getSeller(),
            'buyer' => $object->getBuyer(),
            'token' => $object->getToken(),
            'quantity' => $object->getQuantity(),
            'asset' => $asset,
        ];
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Order;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
