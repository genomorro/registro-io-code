<?php

namespace App\Service;

use Symfony\Component\Uid\Uuid;

class UuidEncoder
{
    private string $key;

    public function __construct(string $appSecret)
    {
        $this->key = substr(hash('sha256', $appSecret, true), 0, 16);
    }

    public function encode(int $id): string
    {
        $payload = pack('Q', $id) . str_repeat("\0", 8);
        $encrypted = openssl_encrypt($payload, 'aes-128-ecb', $this->key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
        return Uuid::fromBinary($encrypted)->toString();
    }

    public function decode(string $uuid): ?int
    {
        try {
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid)) {
                return null;
            }
            $binary = Uuid::fromString($uuid)->toBinary();
            $decrypted = openssl_decrypt($binary, 'aes-128-ecb', $this->key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
            if ($decrypted === false) {
                return null;
            }
            $data = unpack('Q', substr($decrypted, 0, 8));
            return $data[1] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
