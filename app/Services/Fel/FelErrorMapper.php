<?php

namespace App\Services\Fel;

use Throwable;

class FelErrorMapper
{
    public function map(Throwable $e): array
    {
        $message = trim($e->getMessage());
        $lower = mb_strtolower($message);

        return match (true) {
            str_contains($lower, 'issueddatetime'),
            str_contains($lower, 'fecha de emision'),
            str_contains($lower, 'fecha emisión'),
            str_contains($lower, 'issued_at') => [
                'code' => 'FEL_DATE_INVALID',
                'message' => $message,
                'hint' => 'Verifica la fecha certificada y el formato exacto del DTE o anulación.',
            ],

            str_contains($lower, 'nit'),
            str_contains($lower, 'cf'),
            str_contains($lower, 'receptor'),
            str_contains($lower, 'rtu') => [
                'code' => 'FEL_RECEIVER_INVALID',
                'message' => $message,
                'hint' => 'Revisa NIT o CF del receptor y su consistencia fiscal.',
            ],

            str_contains($lower, 'credencial'),
            str_contains($lower, 'usuario'),
            str_contains($lower, 'password'),
            str_contains($lower, 'auth') => [
                'code' => 'FEL_AUTH_ERROR',
                'message' => $message,
                'hint' => 'Verifica credenciales y ambiente FEL activo.',
            ],

            str_contains($lower, 'tipo_personeria'),
            str_contains($lower, 'personeria'),
            str_contains($lower, 'afiliacion_iva') => [
                'code' => 'FEL_EMITTER_CONFIG_INVALID',
                'message' => $message,
                'hint' => 'Revisa la configuración fiscal del emisor en FEL.',
            ],

            str_contains($lower, 'timeout'),
            str_contains($lower, 'timed out'),
            str_contains($lower, 'curl'),
            str_contains($lower, 'conex') => [
                'code' => 'FEL_NETWORK_ERROR',
                'message' => $message,
                'hint' => 'El certificador no respondió correctamente. Intenta nuevamente.',
            ],

            str_contains($lower, 'uuid'),
            str_contains($lower, 'authnumber') => [
                'code' => 'FEL_DOCUMENT_REFERENCE_ERROR',
                'message' => $message,
                'hint' => 'Revisa UUID, serie y número del documento relacionado.',
            ],

            default => [
                'code' => 'FEL_UNKNOWN_ERROR',
                'message' => $message,
                'hint' => 'Revisa el detalle técnico en los eventos y payloads FEL.',
            ],
        };
    }
}