<?php

namespace WtfPhp\JsonApiErrors\Responses;

use WtfPhp\JsonApiErrors\Models\JsonApiError;

/**
 * Class JsonApiErrorResponseSchema
 * @package WtfPhp\JsonApiErrors
 */
class JsonApiErrorResponseSchema
{
    /**
     * @param JsonApiError $jsonApiError
     * @return string
     */
    public function getAsJsonApiError(JsonApiError $jsonApiError): string
    {
        $error = [];

        foreach (get_object_vars($jsonApiError) as $key => $value) {
            $error = $this->addProperty($error, $key, $value);
        }

        return json_encode(['errors' => [$error]]);
    }

    /**
     * @param array $error
     * @param string $key
     * @param string|array $value
     * @return array
     */
    private function addProperty(array $error, string $key, $value): array
    {
        if (!empty($value)) {
            $error = array_merge($error, [$key => $value]);
        }
        return $error;
    }
}
