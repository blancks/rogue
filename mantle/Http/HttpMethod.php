<?php

declare(strict_types=1);

namespace Rogue\Mantle\Http;

enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case DELETE = 'DELETE';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case OPTIONS = 'OPTIONS';
    case HEAD = 'HEAD';
}
