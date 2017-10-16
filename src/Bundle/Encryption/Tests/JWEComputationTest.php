<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2017 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Jose\Bundle\Encryption\Tests;

use Jose\Component\Core\Converter\JsonConverter;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group Bundle
 * @group Functional
 */
final class JWEComputationTest extends WebTestCase
{
    public function testCreateAndLoadAToken()
    {
        $client = static::createClient();
        $container = $client->getContainer();

        $jwk = JWK::create([
            'kty' => 'oct',
            'k' => '3pWc2vAZpHoV7XmCT-z2hWhdQquwQwW5a3XTojbf87c',
        ]);

        /** @var JWEBuilder $builder */
        $builder = $container->get('jose.jwe_builder.builder1');

        /** @var JWEDecrypter $loader */
        $loader = $container->get('jose.jwe_decrypter.loader1');

        $serializer = new CompactSerializer(new JsonConverter());

        $jwe = $builder
            ->create()
            ->withPayload('Hello World!')
            ->withSharedProtectedHeaders([
                'alg' => 'A256KW',
                'enc' => 'A256CBC-HS512',
            ])
            ->addRecipient($jwk)
            ->build();
        $token = $serializer->serialize($jwe, 0);

        $loaded = $serializer->unserialize($token);
        $loaded = $loader->decryptUsingKey($loaded, $jwk, $index);
        self::assertEquals(0, $index);
        self::assertEquals('Hello World!', $loaded->getPayload());
    }
}
