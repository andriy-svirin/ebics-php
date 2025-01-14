<?php

namespace EbicsApi\Ebics\Factories;

use EbicsApi\Ebics\Builders\Request\RequestBuilder;
use EbicsApi\Ebics\Factories\Crypt\BigIntegerFactory;
use EbicsApi\Ebics\Handlers\AuthSignatureHandler;
use EbicsApi\Ebics\Handlers\AuthSignatureHandlerV30;
use EbicsApi\Ebics\Handlers\OrderDataHandler;
use EbicsApi\Ebics\Handlers\OrderDataHandlerV30;
use EbicsApi\Ebics\Handlers\ResponseHandler;
use EbicsApi\Ebics\Handlers\ResponseHandlerV30;
use EbicsApi\Ebics\Handlers\UserSignatureHandler;
use EbicsApi\Ebics\Handlers\UserSignatureHandlerV3;
use EbicsApi\Ebics\Models\Bank;
use EbicsApi\Ebics\Models\Keyring;
use EbicsApi\Ebics\Models\User;
use EbicsApi\Ebics\Services\CryptService;
use EbicsApi\Ebics\Services\DigestResolver;
use EbicsApi\Ebics\Services\DigestResolverV3;
use EbicsApi\Ebics\Services\ZipService;

/**
 * Class Ebics30Factory.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class EbicsFactoryV30 extends EbicsFactory
{
    public function createRequestFactory(
        Bank $bank,
        User $user,
        Keyring $keyring,
        AuthSignatureHandler $authSignatureHandler,
        UserSignatureHandler $userSignatureHandler,
        OrderDataHandler $orderDataHandler,
        DigestResolver $digestResolver,
        RequestBuilder $requestBuilder,
        CryptService $cryptService,
        ZipService $zipService
    ): RequestFactory {
        return new RequestFactoryV30(
            $bank,
            $user,
            $keyring,
            $authSignatureHandler,
            $userSignatureHandler,
            $orderDataHandler,
            $digestResolver,
            $requestBuilder,
            $cryptService,
            $zipService
        );
    }

    public function createAuthSignatureHandler(
        Keyring $keyring,
        CryptService $cryptService
    ): AuthSignatureHandler {
        return new AuthSignatureHandlerV30($keyring, $cryptService);
    }

    public function createUserSignatureHandler(
        User $user,
        Keyring $keyring,
        CryptService $cryptService
    ): UserSignatureHandler {
        return new UserSignatureHandlerV3($user, $keyring, $cryptService);
    }

    public function createOrderDataHandler(
        User $user,
        Keyring $keyring,
        CryptService $cryptService,
        SignatureFactory $signatureFactory,
        CertificateX509Factory $certificateX509Factory,
        BigIntegerFactory $bigIntegerFactory
    ): OrderDataHandler {
        return new OrderDataHandlerV30(
            $user,
            $keyring,
            $cryptService,
            $signatureFactory,
            $certificateX509Factory,
            $bigIntegerFactory
        );
    }

    public function createResponseHandler(
        SegmentFactory $segmentFactory,
        CryptService $cryptService,
        ZipService $zipService,
        BufferFactory $bufferFactory
    ): ResponseHandler {
        return new ResponseHandlerV30(
            $segmentFactory,
            $cryptService,
            $zipService,
            $bufferFactory
        );
    }

    public function createDigestResolver(CryptService $cryptService): DigestResolver
    {
        return new DigestResolverV3($cryptService);
    }
}
