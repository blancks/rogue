<?php

declare(strict_types=1);

namespace Test\Unit\Mantle\Http\Psr7;

use PHPUnit\Framework\TestCase;
use Mantle\Http\Psr7\BasicStringStream;

class BasicStringStreamTest extends TestCase
{
    public function testInitialBufferIsEmptyByDefault()
    {
        $stream = new BasicStringStream();
        $this->assertSame('', $stream->getContents());
        $this->assertSame('', (string)$stream);
        $this->assertSame(0, $stream->getSize());
    }

    public function testInitialBufferWithValue()
    {
        $stream = new BasicStringStream('foo');
        $this->assertSame('foo', $stream->getContents());
        $this->assertSame('foo', (string)$stream);
        $this->assertSame(3, $stream->getSize());
    }

    public function testWriteReplacesBuffer()
    {
        $stream = new BasicStringStream('foo');
        $written = $stream->write('bar');
        $this->assertSame(3, $written);
        $this->assertSame('bar', $stream->getContents());
    }

    public function testReadReturnsBuffer()
    {
        $stream = new BasicStringStream('abc');
        $this->assertSame('abc', $stream->read(10));
    }

    public function testCloseClearsBuffer()
    {
        $stream = new BasicStringStream('abc');
        $stream->close();
        $this->assertSame('', $stream->getContents());
    }

    public function testDetachClearsBufferAndReturnsNull()
    {
        $stream = new BasicStringStream('abc');
        $this->assertNull($stream->detach());
        $this->assertSame('', $stream->getContents());
    }

    public function testIsReadableWritableSeekable()
    {
        $stream = new BasicStringStream('abc');
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertFalse($stream->isSeekable());
    }

    public function testEofAlwaysTrue()
    {
        $stream = new BasicStringStream('abc');
        $this->assertTrue($stream->eof());
    }

    public function testGetMetadataAlwaysNull()
    {
        $stream = new BasicStringStream('abc');
        $this->assertNull($stream->getMetadata());
        $this->assertNull($stream->getMetadata('foo'));
    }

    public function testTellThrowsException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream has no cursor');
        $stream = new BasicStringStream('abc');
        $stream->tell();
    }

    public function testSeekThrowsException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable');
        $stream = new BasicStringStream('abc');
        $stream->seek(0);
    }

    public function testRewindThrowsException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable');
        $stream = new BasicStringStream('abc');
        $stream->rewind();
    }
}
