<?php
declare(strict_types=1);
function normalize_string(mixed $value): string {
    if (!is_string($value)) return '';
    return trim((string) preg_replace('/\s+/u', ' ', $value));
}