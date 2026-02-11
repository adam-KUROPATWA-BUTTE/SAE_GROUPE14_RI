<?php

namespace Service;

class FileService
{
    /**
     * Encode uploaded files to base64 JSON
     */
    public function encodeFiles(
        ?string $photoData = null,
        ?string $cvData = null,
        ?string $conventionData = null,
        ?string $lettreData = null
    ): string {
        $pieces = [];

        if ($photoData !== null) {
            $pieces['photo'] = base64_encode($photoData);
        }
        if ($cvData !== null) {
            $pieces['cv'] = base64_encode($cvData);
        }
        if ($conventionData !== null) {
            $pieces['convention'] = base64_encode($conventionData);
        }
        if ($lettreData !== null) {
            $pieces['lettre_motivation'] = base64_encode($lettreData);
        }

        $result = json_encode($pieces);

        return $result === false ? '' : $result;
    }

    /**
     * Decode files from JSON
     * * @param string $json
     * @return array<string, string>
     */
    public function decodeFiles(string $json): array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Validate and read uploaded file
     * @param array<string, mixed> $file
     * @return string|null
     */
    public function readUploadedFile(array $file): ?string
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if (!is_string($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            return null;
        }

        $content = file_get_contents($file['tmp_name']);
        return $content !== false ? $content : null;
    }
}
