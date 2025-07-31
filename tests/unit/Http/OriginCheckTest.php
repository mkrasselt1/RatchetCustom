<?php
namespace Ratchet\Http;
use Ratchet\AbstractMessageComponentTestCase;

/**
 * @covers Ratchet\Http\OriginCheck
 */
class OriginCheckTest extends AbstractMessageComponentTestCase {
    protected $_reqStub;

    /**
     * @before
     */
    public function setUpConnection() {
        $this->_reqStub = $this->getMockBuilder('Psr\Http\Message\RequestInterface')->getMock();
        $this->_reqStub->expects($this->any())->method('getHeaderLine')->with('Origin')->willReturn('localhost');

        parent::setUpConnection();

        assert($this->_serv instanceof OriginCheck);
        $this->_serv->allowedOrigins[] = 'localhost';
    }

    protected function doOpen($conn) {
        $this->_serv->onOpen($conn, $this->_reqStub);
    }

    public function getConnectionClassString() {
        return 'Ratchet\ConnectionInterface';
    }

    public function getDecoratorClassString() {
        return 'Ratchet\Http\OriginCheck';
    }

    public function getComponentClassString() {
        return 'Ratchet\Http\HttpServerInterface';
    }

    public function testCloseOnNonMatchingOrigin() {
        $this->_serv->allowedOrigins = ['socketo.me'];
        $this->_conn->expects($this->once())->method('close');

        $this->_serv->onOpen($this->_conn, $this->_reqStub);
    }

    public function testCloseOnMissingOrigin() {
        $this->_serv->allowedOrigins = ['socketo.me'];
        $this->_conn->expects($this->once())->method('close');

        $this->_reqStub->expects($this->once())->method('getHeaderLine')->with('Origin')->willReturn('');

        $this->_serv->onOpen($this->_conn, $this->_reqStub);
    }

    public function testCloseOnDuplicateOrigin() {
        $this->_serv->allowedOrigins = ['socketo.me'];
        $this->_conn->expects($this->once())->method('close');

        $this->_reqStub->expects($this->once())->method('getHeaderLine')->with('Origin')->willReturn('http://socketo.me,https://socketo.me');

        $this->_serv->onOpen($this->_conn, $this->_reqStub);
    }

    public function testOnMessage() {
        $this->passthroughMessageTest('Hello World!');
    }
}
