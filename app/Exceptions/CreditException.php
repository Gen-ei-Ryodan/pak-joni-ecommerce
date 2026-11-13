<?php

namespace App\Exceptions;

/**
 * Exception untuk pelanggaran aturan bisnis pada alur pembelian kredit.
 * Pesan exception ditampilkan langsung ke admin sebagai notifikasi.
 */
class CreditException extends \RuntimeException {}
