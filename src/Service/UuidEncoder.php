<?php

namespace App\Service;

use Symfony\Component\Uid\Uuid;

class UuidEncoder
{
    private string $key;

    public function __construct(string $secret)
    {
        // Use the APP_SECRET to derive a 16-byte key for AES-128
        $this->key = substr(hash('sha256', $secret, true), 0, 16);
    }

    /**
     * Encodes an integer ID into a UUID string.
     */
    public function encode(int $id): string
    {
        // Pack ID into 8 bytes (big-endian) and pad with 8 null bytes to reach 16 bytes block size
        $data = pack('J', $id) . str_repeat("\0", 8);
        $encrypted = openssl_encrypt($data, 'AES-128-ECB', $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);

        return Uuid::fromBinary($encrypted)->toRfc4122();
    }

    /**
     * Decodes a UUID string back into an integer ID.
     */
    public function decode(string $uuid): ?int
    {
        if (!Uuid::isValid($uuid)) {
            return null;
        }

        try {
            $binary = Uuid::fromString($uuid)->toBinary();
            $decrypted = openssl_decrypt($binary, 'AES-128-ECB', $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);

            if ($decrypted === false) {
                return null;
            }

            $unpacked = unpack('J', substr($decrypted, 0, 8));
            return $unpacked[1] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
