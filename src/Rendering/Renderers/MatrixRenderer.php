<?php

namespace JibayMcs\SurveyJs\Rendering\Renderers;

use JibayMcs\SurveyJs\Rendering\QuestionRenderer;

class MatrixRenderer extends QuestionRenderer
{
    public function getDisplayValue(): mixed
    {
        return $this->value;
    }

    protected function getViewType(): string
    {
        return 'matrix';
    }

    protected function getExtraData(): array
    {
        $matrixType = $this->question['type'] ?? 'matrix';

        $columns = $this->resolveColumns($this->question['columns'] ?? []);

        if ($matrixType === 'matrixdynamic' || $matrixType === 'matrixdropdown') {
            return $this->buildDynamicMatrix($matrixType, $columns);
        }

        $rows = array_map(
            fn (mixed $row) => [
                'value' => $this->resolveChoiceValue($row),
                'text' => $this->resolveChoiceText($row),
            ],
            $this->question['rows'] ?? [],
        );

        $cells = $this->buildCells($rows, $columns);

        return [
            'columns' => $columns,
            'rows' => $rows,
            'cells' => $cells,
            'matrixType' => $matrixType,
        ];
    }

    /**
     * Resolve columns from SurveyJS format (name/title) to renderer format (value/text).
     * Skips columns with visible=false.
     */
    protected function resolveColumns(array $rawColumns): array
    {
        $columns = [];

        foreach ($rawColumns as $column) {
            if (is_array($column)) {
                if (isset($column['visible']) && $column['visible'] === false) {
                    continue;
                }

                $columns[] = [
                    'value' => $column['name'] ?? $column['value'] ?? '',
                    'text' => $this->resolveLocale($column['title'] ?? null)
                        ?? $column['name'] ?? $column['value'] ?? '',
                ];
            } else {
                $columns[] = [
                    'value' => $column,
                    'text' => (string) $column,
                ];
            }
        }

        return $columns;
    }

    protected function buildDynamicMatrix(string $matrixType, array $columns): array
    {
        $values = is_array($this->value) ? $this->value : [];

        // matrixdynamic: numerically indexed array
        // matrixdropdown: associative array keyed by row name
        $isNumeric = $matrixType === 'matrixdynamic';

        if ($isNumeric) {
            $values = array_values($values);
        }

        // Infer columns from value keys if not defined in JSON
        if (empty($columns) && ! empty($values)) {
            $keys = [];
            foreach ($values as $row) {
                if (is_array($row)) {
                    foreach (array_keys($row) as $key) {
                        $keys[$key] = true;
                    }
                }
            }
            $columns = array_map(fn (string $key) => [
                'value' => $key,
                'text' => $key,
            ], array_keys($keys));
        }

        $rows = [];
        $cells = [];

        if ($isNumeric) {
            foreach ($values as $index => $rowData) {
                $rows[] = [
                    'value' => $index,
                    'text' => '#' . ($index + 1),
                ];

                if (is_array($rowData)) {
                    foreach ($rowData as $colKey => $cellValue) {
                        $cells[$index][$colKey] = $cellValue;
                    }
                }
            }
        } else {
            // matrixdropdown: use defined rows
            $rawRows = $this->question['rows'] ?? [];
            foreach ($rawRows as $rawRow) {
                $rowValue = $this->resolveChoiceValue($rawRow);
                $rows[] = [
                    'value' => $rowValue,
                    'text' => $this->resolveChoiceText($rawRow),
                ];

                $rowData = $values[$rowValue] ?? [];
                if (is_array($rowData)) {
                    foreach ($rowData as $colKey => $cellValue) {
                        $cells[$rowValue][$colKey] = $cellValue;
                    }
                }
            }
        }

        return [
            'columns' => $columns,
            'rows' => $rows,
            'cells' => $cells,
            'matrixType' => $matrixType,
        ];
    }

    protected function buildCells(array $rows, array $columns): array
    {
        $cells = [];

        if (! is_array($this->value)) {
            return $cells;
        }

        foreach ($rows as $row) {
            $rowValue = $row['value'];
            $selectedColumnValue = $this->value[$rowValue] ?? null;

            foreach ($columns as $column) {
                $colValue = $column['value'];
                $cells[$rowValue][$colValue] = $selectedColumnValue === $colValue;
            }
        }

        return $cells;
    }
}
