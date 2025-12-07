<?php

declare(strict_types=1);

namespace Sabre\DAV\Locks;

use Sabre\DAV;
use Sabre\HTTP;

class Issue1591Test extends DAV\AbstractServerTestCase
{
    /**
     * @var Plugin
     */
    protected $locksPlugin;

    public function setup(): void
    {
        parent::setUp();
        $locksBackend = new Backend\File(\Sabre\TestUtil::SABRE_TEMPDIR.'/locksdb');
        $locksPlugin = new Plugin($locksBackend);
        $this->server->addPlugin($locksPlugin);
        $this->locksPlugin = $locksPlugin;
    }

    public function testLockUriEncoding()
    {
        $request = new HTTP\Request('LOCK', '/test%201.txt');
        $request->setBody('<?xml version="1.0"?>
<D:lockinfo xmlns:D="DAV:">
    <D:lockscope><D:exclusive/></D:lockscope>
    <D:locktype><D:write/></D:locktype>
    <D:owner>test</D:owner>
</D:lockinfo>');

        $this->server->httpRequest = $request;
        $this->response = new HTTP\ResponseMock();
        $this->server->httpResponse = $this->response;

        $this->server->exec();

        $body = $this->response->getBodyAsString();

        self::assertEquals('application/xml; charset=utf-8', $this->response->getHeader('Content-Type'));
        self::assertEquals(201, $this->response->status, 'Full response: '.$body);
        self::assertStringContainsString('<d:href>/test%201.txt</d:href>', $body);
echo $body;
        self::assertTrue(file_exists(\Sabre\TestUtil::SABRE_TEMPDIR.'/test 1.txt'));
    }
}
