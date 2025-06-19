<?php

declare(strict_types=1);

namespace Rogue\Mantle\Http\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Class BasicStringStream
 *
 * Implements a non-seekable, readable, and writable stream using a string buffer.
 */
final class BasicStringStream implements StreamInterface
{
    /**
     * BasicStringStream constructor.
     *
     * @param string $buffer Initial buffer contents (optional).
     */
    public function __construct(private string $buffer = '')
    {
        $this->buffer = $buffer;
    }

    /**
     * Returns the entire buffer as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getContents();
    }

    /**
     * Gets the contents of the buffer.
     *
     * @return string
     */
    public function getContents(): string
    {
        return $this->buffer;
    }

    /**
     * Closes the stream and clears the buffer.
     *
     * @return void
     */
    public function close(): void
    {
        $this->buffer = '';
    }

    /**
     * Detaches the buffer and closes the stream.
     *
     * @return null Always returns null.
     */
    public function detach(): null
    {
        $this->close();
        return null;
    }

    /**
     * Gets the size of the buffer in bytes.
     *
     * @return int
     */
    public function getSize(): int
    {
        return strlen($this->buffer);
    }

    /**
     * Checks if the stream is readable.
     *
     * @return bool
     */
    public function isReadable(): bool
    {
        return true;
    }

    /**
     * Checks if the stream is writable.
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        return true;
    }

    /**
     * Checks if the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable(): bool
    {
        return false;
    }

    /**
     * Rewinds the stream to the beginning (not supported).
     *
     * @return void
     * @throws \RuntimeException Always, since the stream is not seekable.
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * Seeks to a position in the stream (not supported).
     *
     * @param int $offset
     * @param int $whence
     * @return void
     * @throws \RuntimeException Always, since the stream is not seekable.
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        throw new \RuntimeException('Stream is not seekable');
    }

    /**
     * Checks if the end of the stream has been reached.
     *
     * @return bool Always true, as the stream has no cursor.
     */
    public function eof(): bool
    {
        return true;
    }

    /**
     * Returns the current position of the file pointer (not supported).
     *
     * @return int
     * @throws \RuntimeException Always, since the stream has no cursor.
     */
    public function tell(): int
    {
        throw new \RuntimeException('Stream has no cursor');
    }

    /**
     * Reads data from the buffer.
     *
     * @param int $length Number of bytes to read (ignored).
     * @return string The buffer contents.
     */
    public function read(int $length): string
    {
        return $this->buffer;
    }

    /**
     * Writes data to the buffer, replacing its contents.
     *
     * @param string $string The string to write.
     * @return int The number of bytes written.
     */
    public function write(string $string): int
    {
        $this->buffer = $string;
        return strlen($string);
    }

    /**
     * Gets metadata about the stream (not supported).
     *
     * @param string|null $key
     * @return null Always returns null.
     */
    public function getMetadata(?string $key = null)
    {
        return null;
    }
}
