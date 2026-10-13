<?php

namespace App\Exceptions;

use RuntimeException;

/** Kesalahan bisnis pada pengajuan (bentrok, ruangan nonaktif, dll). Pesannya aman ditampilkan ke pengguna. */
class BookingException extends RuntimeException
{
}
