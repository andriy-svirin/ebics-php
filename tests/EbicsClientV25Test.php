<?php

namespace EbicsApi\Ebics\Tests;

use DateTime;
use EbicsApi\Ebics\Contexts\FDLContext;
use EbicsApi\Ebics\Contexts\FULContext;
use EbicsApi\Ebics\Contexts\HVDContext;
use EbicsApi\Ebics\Contexts\HVEContext;
use EbicsApi\Ebics\Contexts\HVTContext;
use EbicsApi\Ebics\Contexts\RequestContext;
use EbicsApi\Ebics\Exceptions\InvalidUserOrUserStateException;
use EbicsApi\Ebics\Factories\DocumentFactory;
use Silarhi\Cfonb\CfonbParser;

/**
 * Class EbicsClientTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @group V25
 */
class EbicsClientV25Test extends AbstractEbicsTestCase
{
    /**
     * @dataProvider serversDataProvider
     *
     * @group check-keyring
     */
    public function testCheckKeyring(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId);

        $this->assertTrue($client->checkKeyring());

        $keyring = $client->getKeyring();
        $keyring->setPassword('incorrect_password');

        $this->assertFalse($client->checkKeyring());
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group change-keyring-password
     */
    public function testChangeKeyringPassword(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId);

        $client->changeKeyringPassword('some_new_password');

        $hpb = $client->HPB();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hpb->getTransaction()->getInitializationSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hpb->getTransaction()->getInitializationSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HEV
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHEV(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['HEV']['fake']);
        $hev = $client->HEV();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH000ReturnCode($hev);
        $reportText = $responseHandler->retrieveH000ReportText($hev);
        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group INI
     * @group INI-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testINI(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['INI']['fake']);

        // Check that keyring is empty and or wait on success or wait on exception.
        $userExists = $client->getKeyring()->getUserSignatureA();
        if ($userExists) {
            $this->expectException(InvalidUserOrUserStateException::class);
            $this->expectExceptionCode(91002);
        }
        $ini = $client->INI();
        if (!$userExists) {
            $responseHandler = $client->getResponseHandler();
            $this->saveKeyring($credentialsId, $client->getKeyring());
            $code = $responseHandler->retrieveH00XReturnCode($ini);
            $reportText = $responseHandler->retrieveH00XReportText($ini);
            $this->assertResponseOk($code, $reportText);
        }
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HIA
     * @group HIA-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHIA(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['HIA']['fake']);

        // Check that keyring is empty and or wait on success or wait on exception.
        $bankExists = $client->getKeyring()->getUserSignatureX();
        if ($bankExists) {
            $this->expectException(InvalidUserOrUserStateException::class);
            $this->expectExceptionCode(91002);
        }
        $hia = $client->HIA();
        if (!$bankExists) {
            $responseHandler = $client->getResponseHandler();
            $this->saveKeyring($credentialsId, $client->getKeyring());
            $code = $responseHandler->retrieveH00XReturnCode($hia);
            $reportText = $responseHandler->retrieveH00XReportText($hia);
            $this->assertResponseOk($code, $reportText);
        }
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group H3K
     * @group V25
     * @group H3K-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testH3K(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['H3K']['fake']);

        // Check that keyring is empty and or wait on success or wait on exception.
        $userExists = $client->getKeyring()->getUserSignatureA();
        if ($userExists) {
            $this->expectException(InvalidUserOrUserStateException::class);
            $this->expectExceptionCode(91002);
        }
        $ini = $client->INI();
        if (!$userExists) {
            $responseHandler = $client->getResponseHandler();
            $this->saveKeyring($credentialsId, $client->getKeyring());
            $code = $responseHandler->retrieveH00XReturnCode($ini);
            $reportText = $responseHandler->retrieveH00XReportText($ini);
            $this->assertResponseOk($code, $reportText);
        }
    }

    /**
     * Run first HIA and Activate account in bank panel.
     *
     * @dataProvider serversDataProvider
     *
     * @group HPB
     * @group HPB-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHPB(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['HPB']['fake']);

        $this->assertExceptionCode($codes['HPB']['code']);

        $hpb = $client->HPB();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hpb->getTransaction()->getInitializationSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hpb->getTransaction()->getInitializationSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);
        $this->saveKeyring($credentialsId, $client->getKeyring());
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group SPR
     * @group SPR-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testSPR(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['SPR']['fake']);

        $this->assertExceptionCode($codes['SPR']['code']);
        $spr = $client->SPR();

        $responseHandler = $client->getResponseHandler();

        $code = $responseHandler->retrieveH00XReturnCode($spr->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($spr->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HKD
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHKD(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['HKD']['fake']);

        $this->assertExceptionCode($codes['HKD']['code']);
        $hkd = $client->HKD();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hkd->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hkd->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($hkd->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($hkd->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HTD
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHTD(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['HTD']['fake']);

        $this->assertExceptionCode($codes['HTD']['code']);
        $htd = $client->HTD();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($htd->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($htd->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($htd->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($htd->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group PTK
     * @group PTK-V2
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testPTK(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['PTK']['fake']);

        $this->assertExceptionCode($codes['PTK']['code']);
        $ptk = $client->PTK();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($ptk->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($ptk->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($ptk->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($ptk->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HPD
     * @group HPD-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHPD(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['HPD']['fake']);

        $this->assertExceptionCode($codes['HPD']['code']);
        $hpd = $client->HPD();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hpd->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hpd->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($hpd->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($hpd->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HAA
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHAA(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['HAA']['fake']);

        $this->assertExceptionCode($codes['HAA']['code']);
        $haa = $client->HAA();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($haa->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($haa->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($haa->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($haa->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group VMK
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testVMK(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['VMK']['fake']);

        $this->assertExceptionCode($codes['VMK']['code']);
        $vmk = $client->VMK(new DateTime('2020-03-21'), new DateTime('2020-04-21'));

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($vmk->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($vmk->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($vmk->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($vmk->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group STA
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testSTA(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['STA']['fake']);

        $this->assertExceptionCode($codes['STA']['code']);
        $sta = $client->STA(new DateTime('2020-03-21'), new DateTime('2020-04-21'));

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($sta->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($sta->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($sta->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($sta->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group BKA
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testBKA(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['BKA']['fake']);

        $this->assertExceptionCode($codes['BKA']['code']);
        $bka = $client->BKA(new DateTime('2020-03-21'), new DateTime('2020-04-21'));

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($bka->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($bka->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($bka->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($bka->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group C52
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testC52(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['C52']['fake']);

        $this->assertExceptionCode($codes['C52']['code']);
        $c52 = $client->C52(new DateTime('2020-03-21'), new DateTime('2020-04-21'));

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($c52->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($c52->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($c52->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($c52->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group C53
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testC53(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['C53']['fake']);

        $this->assertExceptionCode($codes['C53']['code']);
        $c53 = $client->C53(new DateTime('2020-03-21'), new DateTime('2020-04-21'));

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($c53->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($c53->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($c53->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($c53->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group C54
     * @group C54-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testC54(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['C54']['fake']);

        $this->assertExceptionCode($codes['C54']['code']);
        $c54 = $client->C54(
            new DateTime('2020-03-21'),
            new DateTime('2020-04-21'),
            (new RequestContext())->setWithES(true)
        );

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($c54->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($c54->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($c54->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($c54->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group Z52
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testZ52(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['Z52']['fake']);

        $this->assertExceptionCode($codes['Z52']['code']);
        $z52 = $client->Z52(new DateTime('2020-03-21'), new DateTime('2020-04-21'));

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($z52->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($z52->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($z52->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($z52->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group Z53
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testZ53(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['Z53']['fake']);

        $this->assertExceptionCode($codes['Z53']['code']);
        $z53 = $client->Z53(new DateTime('2020-03-21'), new DateTime('2020-04-21'));

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($z53->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($z53->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($z53->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($z53->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group Z54
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testZ54(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['Z54']['fake']);

        $this->assertExceptionCode($codes['Z54']['code']);
        $z54 = $client->Z54(new DateTime('2020-03-21'), new DateTime('2020-04-21'));

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($z54->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($z54->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($z54->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($z54->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group FDL
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testFDL(int $credentialsId, array $codes)
    {
        foreach ($codes['FDL'] as $fileFormat => $code) {
            $client = $this->setupClientV25($credentialsId, $code['fake']);

            $this->assertExceptionCode($code['code']);

            $context = (new FDLContext())
                ->setFileFormat($fileFormat)
                ->setParameter('TEST', 'TRUE')
                ->setCountryCode('FR');

            $fdl = $client->FDL(
                $context,
                new DateTime('2020-03-21'),
                new DateTime('2020-04-21')
            );

            $parser = new CfonbParser();
            switch ($fileFormat) {
                case 'camt.xxx.cfonb120.stm':
                    $statements = $parser->read120C($fdl->getData());
                    self::assertNotEmpty($statements);
                    break;
                case 'camt.xxx.cfonb240.act':
                    $statements = $parser->read240C($fdl->getData());
                    self::assertNotEmpty($statements);
                    break;
            }

            $responseHandler = $client->getResponseHandler();
            $code = $responseHandler->retrieveH00XReturnCode($fdl->getTransaction()->getLastSegment()->getResponse());
            $reportText = $responseHandler->retrieveH00XReportText($fdl->getTransaction()->getLastSegment()->getResponse());
            $this->assertResponseOk($code, $reportText);

            $code = $responseHandler->retrieveH00XReturnCode($fdl->getTransaction()->getReceipt());
            $reportText = $responseHandler->retrieveH00XReportText($fdl->getTransaction()->getReceipt());

            $this->assertResponseDone($code, $reportText);
        }
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group FUL
     * @group FUL-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testFUL(int $credentialsId, array $codes)
    {
        $documentFactory = new DocumentFactory();
        foreach ($codes['FUL'] as $fileFormat => $code) {
            $client = $this->setupClientV25($credentialsId, $code['fake']);

            $this->assertExceptionCode($code['code']);

            $context = (new FULContext())
                ->setFileFormat($fileFormat)
                ->setParameter('TEST', 'TRUE')
                ->setCountryCode('FR');

            $ful = $client->FUL(
                $context,
                $documentFactory->createXml($code['document'])
            );

            $responseHandler = $client->getResponseHandler();
            $code = $responseHandler->retrieveH00XReturnCode($ful->getTransaction()->getLastSegment()->getResponse());
            $reportText = $responseHandler->retrieveH00XReportText($ful->getTransaction()->getLastSegment()->getResponse());
            $this->assertResponseOk($code, $reportText);

            $code = $responseHandler->retrieveH00XReturnCode($ful->getTransaction()->getInitialization()->getResponse());
            $reportText = $responseHandler->retrieveH00XReportText($ful->getTransaction()->getInitialization()->getResponse());

            $this->assertResponseOk($code, $reportText);
        }
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group CCT
     * @group V2
     * @group CCT-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testCCT(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['CCT']['fake']);

        $this->assertExceptionCode($codes['CCT']['code']);

        $orderData = $this->buildCustomerCreditTransfer('urn:iso:std:iso:20022:tech:xsd:pain.001.001.03');

        $cct = $client->CCT($orderData);

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($cct->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($cct->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($cct->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($cct->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group CIP
     * @group CIP-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testCIP(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['CIP']['fake']);

        $this->assertExceptionCode($codes['CIP']['code']);

        $customerDirectDebit = $this->buildCustomerDirectDebit('urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');

        $cip = $client->CIP($customerDirectDebit, (new RequestContext())->setWithES(true));

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($cip->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($cip->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($cip->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($cip->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group XE2
     * @group V2
     * @group XE2-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testXE2(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['XE2']['fake']);

        $this->assertExceptionCode($codes['XE2']['code']);

        $orderData = $this->buildCustomerCreditTransfer('urn:iso:std:iso:20022:tech:xsd:pain.001.001.02');

        $xe2 = $client->XE2($orderData);

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($xe2->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($xe2->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($xe2->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($xe2->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group XE3
     * @group V2
     * @group XE3-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testXE3(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['XE3']['fake']);

        $this->assertExceptionCode($codes['XE3']['code']);

        $orderData = $this->buildCustomerCreditTransfer('urn:iso:std:iso:20022:tech:xsd:pain.001.001.02');

        $xe3 = $client->XE3($orderData);

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($xe3->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($xe3->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($xe3->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($xe3->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group CDD
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testCDD(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['CDD']['fake']);

        $this->assertExceptionCode($codes['CDD']['code']);

        $orderData = $this->buildCustomerDirectDebit('urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');

        $cdd = $client->CDD($orderData);

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($cdd->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($cdd->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($cdd->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($cdd->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group CDB
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testCDB(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['CDB']['fake']);

        $this->assertExceptionCode($codes['CDB']['code']);

        $orderData = $this->buildCustomerDirectDebit('urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');

        $cdb = $client->CDB($orderData);

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($cdb->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($cdb->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($cdb->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($cdb->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HVU
     * @group V2
     * @group HVU-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHVU(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['HVU']['fake']);

        $this->assertExceptionCode($codes['HVU']['code']);
        $hvu = $client->HVU();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hvu->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hvu->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($hvu->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($hvu->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HVZ
     * @group V2
     * @group HVZ-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHVZ(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['HVZ']['fake']);

        $this->assertExceptionCode($codes['HVZ']['code']);
        $hvz = $client->HVZ();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hvz->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hvz->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($hvz->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($hvz->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HVE
     * @group V2
     * @group HVE-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHVE(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['HVE']['fake']);

        $this->assertExceptionCode($codes['HVE']['code']);

        $context = new HVEContext();
        $context->setOrderId('V234');
        $context->setServiceName('SDD');
        $context->setOrderType('CDX');
        $context->setScope('DE');
        $context->setServiceOption('0CDX');
        $context->setMsgName('pain.008');
        $context->setPartnerId('PARTNERPK56');
        $context->setDigest('--digset--');

        $hve = $client->HVE($context);

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hve->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hve->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($hve->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hve->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HVD
     * @group V2
     * @group HVD-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHVD(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['HVD']['fake']);

        $this->assertExceptionCode($codes['HVD']['code']);

        $context = new HVDContext();
        $context->setOrderId('V234');
        $context->setOrderType('HVD');
        $context->setMsgName('pain.008');
        $context->setPartnerId('PARTNERPK56');

        $hvd = $client->HVD($context);

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hvd->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hvd->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($hvd->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($hvd->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HVT
     * @group V2
     * @group HVT-V25
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHVT(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV25($credentialsId, $codes['HVT']['fake']);

        $this->assertExceptionCode($codes['HVT']['code']);

        $context = new HVTContext();
        $context->setOrderId('V234');
        $context->setOrderType('HVT');
        $context->setMsgName('pain.008');
        $context->setPartnerId('PARTNERPK56');
        $context->setCompleteOrderData(false);
        $context->setFetchLimit(1);
        $context->setFetchOffset(0);

        $hvd = $client->HVT($context);

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hvd->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hvd->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($hvd->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($hvd->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * Provider for servers.
     */
    public function serversDataProvider()
    {
        return [
            [
                1, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'H3K' => ['code' => null, 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'SPR' => ['code' => null, 'fake' => true],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HTD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => null, 'fake' => false],
                    'PTK' => ['code' => null, 'fake' => false],
                    'VMK' => ['code' => '090003', 'fake' => false],
                    'STA' => ['code' => '090003', 'fake' => false],
                    'BKA' => ['code' => '091005', 'fake' => false],
                    'Z52' => ['code' => '090005', 'fake' => false],
                    'Z53' => ['code' => '090005', 'fake' => false],
                    'Z54' => ['code' => '090005', 'fake' => false],
                    'C52' => ['code' => '090003', 'fake' => false],
                    'C53' => ['code' => '090003', 'fake' => false],
                    'C54' => ['code' => '090003', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091112', 'fake' => false],
                    ],
                    'FUL' => [
                        'pain.001.001.03.sct' => [
                            'code' => '091112',
                            'fake' => false,
                            'document' => '<?xml version="1.0" encoding="UTF-8"?><Root></Root>',
                        ],
                    ],
                    'CCT' => ['code' => null, 'fake' => false],
                    'XE2' => ['code' => null, 'fake' => false],
                    'XE3' => ['code' => null, 'fake' => false],
                    'CDD' => ['code' => null, 'fake' => false],
                    'CDB' => ['code' => '090003', 'fake' => false],
                    'CIP' => ['code' => '091005', 'fake' => false],
                    'HVU' => ['code' => '090003', 'fake' => false],
                    'HVZ' => ['code' => '090003', 'fake' => false],
                    'HVE' => ['code' => '090003', 'fake' => false],
                    'HVD' => ['code' => '090003', 'fake' => false],
                    'HVT' => ['code' => '090003', 'fake' => false],
                ],
            ],
            [
                2, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'H3K' => ['code' => null, 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'SPR' => ['code' => null, 'fake' => true],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HTD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => '091006', 'fake' => false],
                    'PTK' => ['code' => '090005', 'fake' => false],
                    'VMK' => ['code' => '061002', 'fake' => false],
                    'STA' => ['code' => '061002', 'fake' => false],
                    'BKA' => ['code' => '061002', 'fake' => false],
                    'Z52' => ['code' => '061002', 'fake' => false],
                    'Z53' => ['code' => '061002', 'fake' => false],
                    'Z54' => ['code' => '061002', 'fake' => false],
                    'C52' => ['code' => '061002', 'fake' => false],
                    'C53' => ['code' => '061002', 'fake' => false],
                    'C54' => ['code' => '061002', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '090005', 'fake' => false],
                        'camt.xxx.cfonb240.act' => ['code' => '090005', 'fake' => false],
                    ],
                    'FUL' => [
                        'pain.001.001.03.sct' => [
                            'code' => null,
                            'fake' => false,
                            'document' => '<?xml version="1.0" encoding="UTF-8"?><Root></Root>',
                        ],
                    ],
                    'CCT' => ['code' => null, 'fake' => false],
                    'XE2' => ['code' => null, 'fake' => false],
                    'XE3' => ['code' => null, 'fake' => false],
                    'CDD' => ['code' => null, 'fake' => false],
                    'CDB' => ['code' => null, 'fake' => false],
                    'CIP' => ['code' => null, 'fake' => false],
                    'HVU' => ['code' => '091006', 'fake' => false],
                    'HVZ' => ['code' => '091006', 'fake' => false],
                    'HVE' => ['code' => '091006', 'fake' => false],
                    'HVD' => ['code' => '091006', 'fake' => false],
                    'HVT' => ['code' => '090006', 'fake' => false],
                ],
            ],
            [
                3, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'H3K' => ['code' => null, 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'SPR' => ['code' => null, 'fake' => true],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HTD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => null, 'fake' => false],
                    'PTK' => ['code' => null, 'fake' => false],
                    'VMK' => ['code' => '090003', 'fake' => false],
                    'STA' => ['code' => '090003', 'fake' => false],
                    'BKA' => ['code' => '091005', 'fake' => false],
                    'Z52' => ['code' => '090005', 'fake' => false],
                    'Z53' => ['code' => '090005', 'fake' => false],
                    'Z54' => ['code' => '090005', 'fake' => false],
                    'C52' => ['code' => '090003', 'fake' => false],
                    'C53' => ['code' => '090003', 'fake' => false],
                    'C54' => ['code' => '090003', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091112', 'fake' => false],
                    ],
                    'FUL' => [
                        'pain.001.001.03.sct' => [
                            'code' => '091112',
                            'fake' => false,
                            'document' => '<?xml version="1.0" encoding="UTF-8"?><Root></Root>',
                        ],
                    ],
                    'CCT' => ['code' => null, 'fake' => false],
                    'XE2' => ['code' => null, 'fake' => false],
                    'XE3' => ['code' => null, 'fake' => false],
                    'CDD' => ['code' => null, 'fake' => false],
                    'CDB' => ['code' => null, 'fake' => false],
                    'CIP' => ['code' => '091005', 'fake' => false],
                    'HVU' => ['code' => '090003', 'fake' => false],
                    'HVZ' => ['code' => '090003', 'fake' => false],
                    'HVE' => ['code' => '090003', 'fake' => false],
                    'HVD' => ['code' => '090003', 'fake' => false],
                    'HVT' => ['code' => '090003', 'fake' => false],
                ],
            ],
            [
                4, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'H3K' => ['code' => null, 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'SPR' => ['code' => null, 'fake' => true],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HTD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => null, 'fake' => false],
                    'PTK' => ['code' => null, 'fake' => false],
                    'VMK' => ['code' => '090005', 'fake' => false],
                    'STA' => ['code' => '090005', 'fake' => false],
                    'BKA' => ['code' => '091005', 'fake' => false],
                    'Z52' => ['code' => '090005', 'fake' => false],
                    'Z53' => ['code' => '090005', 'fake' => false],
                    'Z54' => ['code' => '090005', 'fake' => false],
                    'C52' => ['code' => '090003', 'fake' => false],
                    'C53' => ['code' => '090003', 'fake' => false],
                    'C54' => ['code' => '090003', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091112', 'fake' => false],
                    ],
                    'FUL' => [
                        'pain.001.001.03.sct' => [
                            'code' => '091112',
                            'fake' => false,
                            'document' => '<?xml version="1.0" encoding="UTF-8"?><Root></Root>',
                        ],
                    ],
                    'CCT' => ['code' => '090003', 'fake' => false],
                    'XE2' => ['code' => null, 'fake' => false],
                    'XE3' => ['code' => null, 'fake' => false],
                    'CDD' => ['code' => '090003', 'fake' => false],
                    'CDB' => ['code' => '090003', 'fake' => false],
                    'CIP' => ['code' => '091005', 'fake' => false],
                    'HVU' => ['code' => '090003', 'fake' => false],
                    'HVZ' => ['code' => '090003', 'fake' => false],
                    'HVE' => ['code' => '090003', 'fake' => false],
                    'HVD' => ['code' => '090003', 'fake' => false],
                    'HVT' => ['code' => '090003', 'fake' => false],
                ],
            ],
            [
                5, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'H3K' => ['code' => null, 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'SPR' => ['code' => null, 'fake' => true],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HTD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => null, 'fake' => false],
                    'PTK' => ['code' => null, 'fake' => false],
                    'VMK' => ['code' => '090003', 'fake' => false],
                    'STA' => ['code' => '090003', 'fake' => false],
                    'BKA' => ['code' => '091005', 'fake' => false],
                    'Z52' => ['code' => '090005', 'fake' => false],
                    'Z53' => ['code' => '090005', 'fake' => false],
                    'Z54' => ['code' => '090005', 'fake' => false],
                    'C52' => ['code' => '090003', 'fake' => false],
                    'C53' => ['code' => '090003', 'fake' => false],
                    'C54' => ['code' => '090003', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091112', 'fake' => false],
                    ],
                    'FUL' => [
                        'pain.001.001.03.sct' => [
                            'code' => '091112',
                            'fake' => false,
                            'document' => '<?xml version="1.0" encoding="UTF-8"?><Root></Root>',
                        ],
                    ],
                    'CCT' => ['code' => null, 'fake' => false],
                    'XE2' => ['code' => null, 'fake' => false],
                    'XE3' => ['code' => null, 'fake' => false],
                    'CDD' => ['code' => null, 'fake' => false],
                    'CDB' => ['code' => null, 'fake' => false],
                    'CIP' => ['code' => '091005', 'fake' => false],
                    'HVU' => ['code' => '090003', 'fake' => false],
                    'HVZ' => ['code' => '090003', 'fake' => false],
                    'HVE' => ['code' => '090003', 'fake' => false],
                    'HVD' => ['code' => '090003', 'fake' => false],
                    'HVT' => ['code' => '090003', 'fake' => false],
                ],
            ],
        ];
    }
}
