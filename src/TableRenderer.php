<?php

declare(strict_types=1);

namespace JiraReport;

class TableRenderer
{
    /**
     * Renders data as a formatted table
     *
     * @param array $headers The table headers
     * @param array $rows Data to display (array of arrays)
     * @return string Formatted table string
     */
    public function render(array $headers, array $rows): string
    {
        if (empty($rows)) {
            return "No data to display.\n";
        }

        // Calculate column widths
        $columnWidths = array_map('strlen', $headers);
        foreach ($rows as $row) {
            foreach ($row as $columnIndex => $cell) {
                $cellLength = strlen((string)$cell);
                if (!isset($columnWidths[$columnIndex]) || $cellLength > $columnWidths[$columnIndex]) {
                    $columnWidths[$columnIndex] = $cellLength;
                }
            }
        }

        // Build the table
        $output = $this->renderSeparator($columnWidths);
        $output .= $this->renderRow($headers, $columnWidths, true);
        $output .= $this->renderSeparator($columnWidths);

        foreach ($rows as $row) {
            $output .= $this->renderRow($row, $columnWidths);
        }

        $output .= $this->renderSeparator($columnWidths);
        
        return $output;
    }

    /**
     * Renders a horizontal separator line
     *
     * @param array $columnWidths Array of column widths
     * @return string Formatted separator line
     */
    private function renderSeparator(array $columnWidths): string
    {
        $parts = [];
        foreach ($columnWidths as $width) {
            $parts[] = str_repeat('-', $width + 2);
        }
        return '+' . implode('+', $parts) . "+\n";
    }

    /**
     * Renders a single row of the table
     *
     * @param array $row The row data
     * @param array $columnWidths Array of column widths
     * @param bool $isHeader Whether this is a header row (for styling)
     * @return string Formatted row
     */
    private function renderRow(array $row, array $columnWidths, bool $isHeader = false): string
    {
        $formattedRow = [];
        foreach ($row as $columnIndex => $cell) {
            $cellStr = (string)$cell;
            $width = $columnWidths[$columnIndex] ?? strlen($cellStr);
            
            // Right-align numeric cells, left-align text
            $isNumeric = is_numeric($cell) && !$isHeader;
            if ($isNumeric) {
                $formattedRow[] = ' ' . str_pad($cellStr, $width, ' ', STR_PAD_LEFT) . ' ';
            } else {
                $formattedRow[] = ' ' . str_pad($cellStr, $width) . ' ';
            }
        }
        return '|' . implode('|', $formattedRow) . "|\n";
    }

    /**
     * Formats a table with colors and styling
     * 
     * @param array $headers The table headers
     * @param array $rows Data to display (array of arrays)
     * @param array $options Formatting options
     * @return string Formatted color table string
     */
    public function renderColorTable(array $headers, array $rows, array $options = []): string
    {
        if (empty($rows)) {
            return "No data to display.\n";
        }

        // Default options
        $options = array_merge([
            'headerStyle' => "\033[1;37m", // Bold white
            'borderColor' => "\033[0;36m", // Cyan
            'resetColor' => "\033[0m",     // Reset
            'alternateRows' => true,       // Use alternating row colors
            'evenRowColor' => "\033[0;37m", // Light gray
            'oddRowColor' => "",           // Default terminal color
        ], $options);

        // Calculate column widths
        $columnWidths = array_map('strlen', $headers);
        foreach ($rows as $row) {
            foreach ($row as $columnIndex => $cell) {
                $cellLength = strlen((string)$cell);
                if (!isset($columnWidths[$columnIndex]) || $cellLength > $columnWidths[$columnIndex]) {
                    $columnWidths[$columnIndex] = $cellLength;
                }
            }
        }

        // Build the table
        $borderColor = $options['borderColor'];
        $resetColor = $options['resetColor'];
        
        // Top border
        $output = $borderColor . $this->renderSeparator($columnWidths) . $resetColor;
        
        // Header row
        $headerRow = $this->renderRow($headers, $columnWidths, true);
        $output .= $borderColor . substr($headerRow, 0, 1) . $resetColor;
        $output .= $options['headerStyle'] . substr($headerRow, 1, -1) . $resetColor;
        $output .= $borderColor . substr($headerRow, -1) . $resetColor;
        
        // Header/data separator
        $output .= $borderColor . $this->renderSeparator($columnWidths) . $resetColor;
        
        // Data rows
        foreach ($rows as $index => $row) {
            $rowColor = ($options['alternateRows'] && $index % 2 === 0) 
                ? $options['evenRowColor'] 
                : $options['oddRowColor'];
                
            $dataRow = $this->renderRow($row, $columnWidths);
            $output .= $borderColor . substr($dataRow, 0, 1) . $resetColor;
            $output .= $rowColor . substr($dataRow, 1, -1) . $resetColor;
            $output .= $borderColor . substr($dataRow, -1) . $resetColor;
        }
        
        // Bottom border
        $output .= $borderColor . $this->renderSeparator($columnWidths) . $resetColor;
        
        return $output;
    }
}