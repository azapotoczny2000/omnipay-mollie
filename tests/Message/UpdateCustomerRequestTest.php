<?php

namespace Omnipay\Mollie\Test\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Mollie\Message\Request\UpdateCustomerRequest;
use Omnipay\Mollie\Message\Response\UpdateCustomerResponse;
use Omnipay\Tests\TestCase;

class UpdateCustomerRequestTest extends TestCase
{
    use AssertRequestTrait;

    /**
     *
     * @var UpdateCustomerRequest
     */
    protected $request;

    public function setUp()
    {
        $this->request = new UpdateCustomerRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->request->initialize(array(
            'apiKey'            => 'mykey',
            'customerReference' => 'cst_bSNBBJBzdG',
            'description'       => 'Jane Doe',
            'email'             => 'john@doe.com',
            'locale'            => 'nl_NL',
            'metadata'          => 'Just some meta data.',
        ));
    }

    /**
     * @throws InvalidRequestException
     */
    public function testData()
    {
        $this->request->initialize(array(
            'apiKey'            => 'mykey',
            'customerReference' => 'cst_bSNBBJBzdG',
            'description'       => 'Jane Doe',
            'email'             => 'john@doe.com',
            'metadata'          => 'Just some meta data.',
        ));

        $data = $this->request->getData();

        $this->assertSame("Jane Doe", $data['name']);
        $this->assertSame('john@doe.com', $data['email']);
        $this->assertSame('Just some meta data.', $data['metadata']);
        $this->assertCount(4, $data);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('UpdateCustomerSuccess.txt');

        /** @var UpdateCustomerResponse $response */
        $response = $this->request->send();

        $this->assertEqualRequest(
            new \GuzzleHttp\Psr7\Request(
                "POST",
                "https://api.mollie.com/v2/customers/cst_bSNBBJBzdG",
                [],
                '{  
                    "name": "Jane Doe",
                    "email": "john@doe.com",
                    "metadata": "Just some meta data.",
                    "locale": "nl_NL"
                }'
            ),
            $this->getMockClient()->getLastRequest()
        );

        $this->assertInstanceOf(UpdateCustomerResponse::class, $response);
        $this->assertSame('cst_bSNBBJBzdG', $response->getCustomerReference());

        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('UpdateCustomerFailure.txt');

        /** @var UpdateCustomerResponse $response */
        $response = $this->request->send();

        $this->assertEqualRequest(new \GuzzleHttp\Psr7\Request("GET", "https://api.mollie.com/v2/customers/cst_bSNBBJBzdG"), $this->getMockClient()->getLastRequest());

        $this->assertInstanceOf(UpdateCustomerResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getCustomerReference());
        $this->assertSame('{"status":401,"title":"Unauthorized Request","detail":"Missing authentication, or failed to authenticate","_links":{"documentation":{"href":"https:\/\/docs.mollie.com\/guides\/authentication","type":"text\/html"}}}', $response->getMessage());
    }
}
