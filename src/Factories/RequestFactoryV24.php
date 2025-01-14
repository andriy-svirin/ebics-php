<?php

namespace EbicsApi\Ebics\Factories;

use EbicsApi\Ebics\Builders\Request\OrderDetailsBuilder;
use EbicsApi\Ebics\Builders\Request\RequestBuilder;
use EbicsApi\Ebics\Builders\Request\XmlBuilderV24;
use EbicsApi\Ebics\Contexts\RequestContext;
use EbicsApi\Ebics\Models\Http\Request;
use EbicsApi\Ebics\Models\UploadTransaction;
use LogicException;

/**
 * Ebics 2.4 RequestFactory.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class RequestFactoryV24 extends RequestFactoryV2
{
    protected function createRequestBuilderInstance(): RequestBuilder
    {
        return $this->requestBuilder
            ->createInstance(function (Request $request) {
                return new XmlBuilderV24($this->zipService, $this->cryptService, $request);
            });
    }

    protected function addOrderType(
        OrderDetailsBuilder $orderDetailsBuilder,
        string $orderType,
        bool $withES = false
    ): OrderDetailsBuilder {
        switch ($orderType) {
            case 'INI':
            case 'HIA':
                $orderAttribute = OrderDetailsBuilder::ORDER_ATTRIBUTE_DZNNN;
                break;
            case 'FUL':
                $orderAttribute = $withES ?
                    OrderDetailsBuilder::ORDER_ATTRIBUTE_OZHNN : OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN;
                break;
            default:
                $orderAttribute = OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN;
        }

        $orderId = $this->cryptService->generateOrderId($this->user->getPartnerId());

        return $orderDetailsBuilder
            ->addOrderType($orderType)
            ->addOrderId($orderId)
            ->addOrderAttribute($orderAttribute);
    }

    public function createVMK(RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createSTA(RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createC52(RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createC53(RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createC54(RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createZ52(RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createZ53(RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createZ54(RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createZSR(RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createXEK(RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createCCT(UploadTransaction $transaction, RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createCDD(UploadTransaction $transaction, RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createCDB(UploadTransaction $transaction, RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createCIP(UploadTransaction $transaction, RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createXE2(UploadTransaction $transaction, RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createXE3(UploadTransaction $transaction, RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }

    public function createYCT(UploadTransaction $transaction, RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.4');
    }
}
