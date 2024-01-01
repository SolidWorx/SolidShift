<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Security\Signature;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use function base64_encode;
use function hash_final;
use function hash_hmac;
use function hash_init;
use function hash_update;

final readonly class SignatureHasher
{
    private const HASH_ALGORITHM = 'sha256';

    public function __construct(
        #[Autowire('%kernel.secret%')]
        private string $secret
    ) {
    }

    /**
     * @param array<string, string|bool|int|null> $data
     */
    public function generate(array $data): string
    {
        $fieldsHash = hash_init(self::HASH_ALGORITHM);

        foreach ($data as $key => $value) {
            hash_update($fieldsHash, ':' . base64_encode($key . '|' . $value));
        }

        $fieldsHash = $this->clean(hash_final($fieldsHash, true));

        return $this->generateHash($fieldsHash . ':') . $fieldsHash;
    }

    private function generateHash(string $tokenValue): string
    {
        return $this->clean(hash_hmac(self::HASH_ALGORITHM, $tokenValue, $this->secret, true));
    }

    private function clean(string $token): string
    {
        return strtr(base64_encode($token), '+/=', '-_~');
    }
}
