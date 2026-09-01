<?php
/**
 * Validator.
 * 
 * I didn't want to pull in a whole package for this.
 * It handles the common rules I actually use: required, email, min/max,
 * unique, confirmed, etc. Returns pretty error messages.
 */
namespace App\Http\Requests;

use App\Core\Exceptions\ValidationException;

class Validator
{
    private array $rules = [];
    private array $messages = [];
    private array $customMessages = [];
    private array $data = [];

    // ── rule registration (fluent API) ───────────────────────────

    public static function make(array $data, array $rules, array $messages = []): self
    {
        $v = new self();
        $v->data = $data;
        $v->rules = $rules;
        $v->customMessages = $messages;
        return $v;
    }

    /** validate and throw on failure */
    public function validate(): array
    {
        $errors = [];

        foreach ($this->rules as $field => $ruleString) {
            $rules = is_string($ruleString) ? explode('|', $ruleString) : (array) $ruleString;
            $value = $this->data[$field] ?? null;
            $niceName = str_replace(['_', '-'], ' ', $field);

            foreach ($rules as $rule) {
                // parse "rule:param1,param2" format
                if (str_contains($rule, ':')) {
                    [$ruleName, $params] = explode(':', $rule, 2);
                    $params = explode(',', $params);
                } else {
                    $ruleName = $rule;
                    $params = [];
                }

                $error = $this->checkRule($ruleName, $field, $niceName, $value, $params);
                if ($error !== null) {
                    $errors[$field][] = $error;
                }
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $this->data;
    }

    /** validate and return errors (no throw) */
    public function fails(): ?array
    {
        try {
            $this->validate();
            return null;
        } catch (ValidationException $e) {
            return $e->getErrors();
        }
    }

    // ── rule implementations ─────────────────────────────────────

    private function checkRule(
        string $rule, string $field, string $niceName,
        mixed $value, array $params
    ): ?string {
        // check custom message first
        $msgKey = "{$field}.{$rule}";
        $custom = $this->customMessages[$msgKey] ?? null;

        return match ($rule) {
            'required' => ($value === null || $value === '')
                ? ($custom ?? "{$niceName} is required.")
                : null,

            'email' => ($value && !filter_var($value, FILTER_VALIDATE_EMAIL))
                ? ($custom ?? "{$niceName} doesn't look like a valid email.")
                : null,

            'min' => ($value !== null && strlen((string) $value) < (int)($params[0] ?? 0))
                ? ($custom ?? "{$niceName} needs to be at least {$params[0]} characters.")
                : null,

            'max' => ($value !== null && strlen((string) $value) > (int)($params[0] ?? 255))
                ? ($custom ?? "{$niceName} can't be longer than {$params[0]} characters.")
                : null,

            'url' => ($value && !filter_var($value, FILTER_VALIDATE_URL))
                ? ($custom ?? "{$niceName} must be a valid URL.")
                : null,

            'numeric' => ($value !== null && !is_numeric($value))
                ? ($custom ?? "{$niceName} must be a number.")
                : null,

            'alpha' => ($value !== null && !preg_match('/^[a-zA-Z]+$/', (string) $value))
                ? ($custom ?? "{$niceName} should only contain letters.")
                : null,

            'alpha_dash' => ($value !== null && !preg_match('/^[a-zA-Z0-9_-]+$/', (string) $value))
                ? ($custom ?? "{$niceName} can only contain letters, numbers, dashes, and underscores.")
                : null,

            'slug' => ($value !== null && !preg_match('/^[a-z0-9-]+$/', (string) $value))
                ? ($custom ?? "{$niceName} must be a valid slug (lowercase, dashes only).")
                : null,

            'in' => ($value !== null && !in_array($value, $params))
                ? ($custom ?? "{$niceName} must be one of: " . implode(', ', $params) . ".")
                : null,

            'confirmed' => (($value ?? '') !== ($this->data["{$field}_confirmation"] ?? ''))
                ? ($custom ?? "{$niceName} confirmation doesn't match.")
                : null,

            'not_empty' => (is_array($value) && empty($value))
                ? ($custom ?? "{$niceName} can't be empty.")
                : null,

            default => null, // unknown rules are just ignored
        };
    }
}
