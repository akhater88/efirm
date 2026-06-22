<?php

namespace App\Services;

/**
 * Recursive condition evaluator with a fixed operator set.
 * NEVER uses eval(). §4.16 rule engine isolation.
 */
class ConditionEvaluatorService
{
    private const MAX_DEPTH = 10;

    /**
     * Fixed set of allowed operators.
     */
    private const ALLOWED_OPERATORS = [
        'eq', 'neq', 'gt', 'lt', 'gte', 'lte',
        'in', 'contains', 'is_null', 'is_not_null',
        'and', 'or', 'not',
    ];

    /**
     * Evaluate a condition tree against a data payload.
     *
     * @param  array<string, mixed>  $condition
     * @param  array<string, mixed>  $data
     */
    public function evaluate(array $condition, array $data, int $depth = 0): bool
    {
        if ($depth > self::MAX_DEPTH) {
            throw new \RuntimeException('Condition evaluation exceeded maximum depth of '.self::MAX_DEPTH);
        }

        if (empty($condition)) {
            return true;
        }

        $operator = $condition['operator'] ?? null;

        if ($operator === null) {
            return true;
        }

        if (! in_array($operator, self::ALLOWED_OPERATORS, true)) {
            throw new \InvalidArgumentException("Unknown condition operator: {$operator}");
        }

        return match ($operator) {
            'eq' => $this->evaluateComparison($condition, $data, fn ($a, $b) => $a == $b),
            'neq' => $this->evaluateComparison($condition, $data, fn ($a, $b) => $a != $b),
            'gt' => $this->evaluateComparison($condition, $data, fn ($a, $b) => $a > $b),
            'lt' => $this->evaluateComparison($condition, $data, fn ($a, $b) => $a < $b),
            'gte' => $this->evaluateComparison($condition, $data, fn ($a, $b) => $a >= $b),
            'lte' => $this->evaluateComparison($condition, $data, fn ($a, $b) => $a <= $b),
            'in' => $this->evaluateIn($condition, $data),
            'contains' => $this->evaluateContains($condition, $data),
            'is_null' => $this->evaluateIsNull($condition, $data),
            'is_not_null' => ! $this->evaluateIsNull($condition, $data),
            'and' => $this->evaluateAnd($condition, $data, $depth),
            'or' => $this->evaluateOr($condition, $data, $depth),
            'not' => $this->evaluateNot($condition, $data, $depth),
        };
    }

    /**
     * @param  array<string, mixed>  $condition
     * @param  array<string, mixed>  $data
     */
    private function evaluateComparison(array $condition, array $data, callable $comparator): bool
    {
        $fieldValue = $this->resolveField($condition['field'] ?? '', $data);
        $targetValue = $condition['value'] ?? null;

        return $comparator($fieldValue, $targetValue);
    }

    /**
     * @param  array<string, mixed>  $condition
     * @param  array<string, mixed>  $data
     */
    private function evaluateIn(array $condition, array $data): bool
    {
        $fieldValue = $this->resolveField($condition['field'] ?? '', $data);
        $values = $condition['value'] ?? [];

        if (! is_array($values)) {
            return false;
        }

        return in_array($fieldValue, $values, false);
    }

    /**
     * @param  array<string, mixed>  $condition
     * @param  array<string, mixed>  $data
     */
    private function evaluateContains(array $condition, array $data): bool
    {
        $fieldValue = $this->resolveField($condition['field'] ?? '', $data);
        $searchValue = $condition['value'] ?? '';

        if (is_array($fieldValue)) {
            return in_array($searchValue, $fieldValue, false);
        }

        if (is_string($fieldValue) && is_string($searchValue)) {
            return str_contains($fieldValue, $searchValue);
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $condition
     * @param  array<string, mixed>  $data
     */
    private function evaluateIsNull(array $condition, array $data): bool
    {
        $fieldValue = $this->resolveField($condition['field'] ?? '', $data);

        return $fieldValue === null;
    }

    /**
     * @param  array<string, mixed>  $condition
     * @param  array<string, mixed>  $data
     */
    private function evaluateAnd(array $condition, array $data, int $depth): bool
    {
        $conditions = $condition['conditions'] ?? [];

        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $sub) {
            if (! $this->evaluate($sub, $data, $depth + 1)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $condition
     * @param  array<string, mixed>  $data
     */
    private function evaluateOr(array $condition, array $data, int $depth): bool
    {
        $conditions = $condition['conditions'] ?? [];

        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $sub) {
            if ($this->evaluate($sub, $data, $depth + 1)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $condition
     * @param  array<string, mixed>  $data
     */
    private function evaluateNot(array $condition, array $data, int $depth): bool
    {
        $inner = $condition['condition'] ?? [];

        return ! $this->evaluate($inner, $data, $depth + 1);
    }

    /**
     * Resolve a dot-notation field path against the data array.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveField(string $field, array $data): mixed
    {
        if ($field === '') {
            return null;
        }

        return data_get($data, $field);
    }
}
