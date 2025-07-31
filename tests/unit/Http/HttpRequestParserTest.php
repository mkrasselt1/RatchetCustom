<?php
namespace Ratchet\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers Ratchet\Http\HttpRequestParser
 */
class HttpRequestParserTest extends TestCase {
    protected $parser;

    /**
     * @before
     */
    public function setUpParser() {
        $this->parser = new HttpRequestParser;
    }

    public function headersProvider() {
        return array(
            array(false, "GET / HTTP/1.1\r\nHost: socketo.me\r\n")
          , array(true,  "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n")
          , array(true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n1")
          , array(true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie✖")
          , array(true,  "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie✖\r\n\r\n")
          , array(true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie\r\n")
        );
    }

    /**
     * @dataProvider headersProvider
     */
    public function testIsEom($expected, $message) {
        $this->assertEquals($expected, $this->parser->isEom($message));
    }

    public function testBufferOverflowResponse() {
        $conn = $this->getMockBuilder('Ratchet\Mock\Connection')->getMock();

        $this->parser->maxSize = 20;

        $this->assertNull($this->parser->onMessage($conn, "GET / HTTP/1.1\r\n"));

        if (method_exists($this, 'expectException')) {
            $this->expectException('OverflowException');
        } else {
            $this->setExpectedException('OverflowException');
        }

        $this->parser->onMessage($conn, "Header-Is: Too Big");
    }

    public function testOnMessageThrowsExceptionForEmptyNewlines() {
        $conn = $this->getMockBuilder('Ratchet\Mock\Connection')->getMock();

        if (method_exists($this, 'expectException')) {
            $this->expectException('InvalidArgumentException');
        } else {
            $this->setExpectedException('InvalidArgumentException');
        }

        $this->parser->onMessage($conn, "\r\n\r\n");
    }

    public function testReturnTypeIsRequest() {
        $conn = $this->getMockBuilder('Ratchet\Mock\Connection')->getMock();
        $return = $this->parser->onMessage($conn, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n");

        $this->assertInstanceOf('Psr\Http\Message\RequestInterface', $return);
    }
}
