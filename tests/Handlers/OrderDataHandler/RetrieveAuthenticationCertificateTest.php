<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\OrderDataHandler;

use AndrewSvirin\Ebics\Factories\CertificateFactory;
use AndrewSvirin\Ebics\Handlers\OrderDataHandler;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\OrderData;
use PHPUnit\Framework\TestCase;

use function base64_encode;

class RetrieveAuthenticationCertificateTest extends TestCase
{
    public function testNotCertified(): void
    {
        $certificat = new Certificate('test', 'test');

        $certificateFactory = self::createMock(CertificateFactory::class);
        $certificateFactory->expects(self::once())->method('buildCertificateXFromDetails')->with('mod', 'expo', null)->willReturn($certificat);

        $sUT = new OrderDataHandler($certificateFactory);

        $orderData = new OrderData('<?xml version="1.0"?>
        <AuthenticationPubKeyInfo xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
            <PubKeyValue xmlns="urn:org:ebics:H004">
                <ds:RSAKeyValue>
                    <ds:Modulus>' . base64_encode('mod') . '</ds:Modulus>
                    <ds:Exponent>' . base64_encode('expo') . '</ds:Exponent>
                </ds:RSAKeyValue>
            </PubKeyValue>
        </AuthenticationPubKeyInfo>
        ');

        self::assertSame($certificat, $sUT->retrieveAuthenticationCertificate($orderData));
    }

    public function testCertified(): void
    {
        $certificat = new Certificate('test', 'test');

        $certificateFactory = self::createMock(CertificateFactory::class);
        $certificateFactory->expects(self::once())->method('buildCertificateXFromDetails')->with('mod', 'expo', 'cert')->willReturn($certificat);

        $sUT = new OrderDataHandler($certificateFactory);

        $orderData = new OrderData('<?xml version="1.0"?>
        <AuthenticationPubKeyInfo xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
            <ds:X509Data>
                 <ds:X509Certificate>' . base64_encode('cert') . '</ds:X509Certificate>
            </ds:X509Data>
            <PubKeyValue xmlns="urn:org:ebics:H004">
                <ds:RSAKeyValue>
                    <ds:Modulus>' . base64_encode('mod') . '</ds:Modulus>
                    <ds:Exponent>' . base64_encode('expo') . '</ds:Exponent>
                </ds:RSAKeyValue>
            </PubKeyValue>
        </AuthenticationPubKeyInfo>
        ');

        self::assertSame($certificat, $sUT->retrieveAuthenticationCertificate($orderData));
    }
}