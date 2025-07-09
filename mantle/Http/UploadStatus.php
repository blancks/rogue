<?php

declare(strict_types=1);

namespace Mantle\Http;

enum UploadStatus: int
{
    case ERR_OK = \UPLOAD_ERR_OK;
    case ERR_INI_SIZE = \UPLOAD_ERR_INI_SIZE;
    case ERR_FORM_SIZE = \UPLOAD_ERR_FORM_SIZE;
    case ERR_PARTIAL = \UPLOAD_ERR_PARTIAL;
    case ERR_NO_FILE = \UPLOAD_ERR_NO_FILE;
    case ERR_NO_TMP_DIR = \UPLOAD_ERR_NO_TMP_DIR;
    case ERR_CANT_WRITE = \UPLOAD_ERR_CANT_WRITE;
    case ERR_EXTENSION = \UPLOAD_ERR_EXTENSION;
}
