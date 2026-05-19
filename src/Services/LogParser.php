<?php

declare(strict_types=1);

namespace Hamzi\CoreWatch\Services;

use DateTime;

class LogParser
{
    /**
     * Parses a specific log file with chunked backward streaming, filters, and pagination.
     *
     * @param  string  $path  Absolute path to the log file.
     * @param  string  $type  The log format type ('laravel', 'nginx_access', 'nginx_error', etc.).
     * @param  int  $limit  Max logs to return.
     * @param  int  $page  Page offset.
     * @param  array<string, mixed>  $filters  Filtering criteria (ip, status, level, search, date_start, date_end).
     * @return array{logs: array<int, array<string, mixed>>, total_scanned: int, has_more: bool}
     */
    public function parse(string $path, string $type, int $limit = 100, int $page = 1, array $filters = []): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            return [
                'logs' => [],
                'total_scanned' => 0,
                'has_more' => false,
                'error' => "Log file does not exist or is not readable: {$path}",
            ];
        }

        $logs = [];
        $chunkSize = 1024 * 64; // 64KB read chunks
        $handle = fopen($path, 'r');
        if (! $handle) {
            return ['logs' => [], 'total_scanned' => 0, 'has_more' => false, 'error' => 'Unable to open file'];
        }

        fseek($handle, 0, SEEK_END);
        $fileSize = ftell($handle);
        $position = $fileSize;

        $leftover = '';
        $matchedCount = 0;
        $skipCount = ($page - 1) * $limit;
        $totalScanned = 0;
        $hasMore = false;

        // Temporal buffer for multi-line logs (like Laravel stack traces)
        $currentStack = [];

        while ($position > 0 && count($logs) < $limit) {
            $readSize = min($position, $chunkSize);
            $position -= $readSize;

            fseek($handle, $position, SEEK_SET);
            $buffer = fread($handle, $readSize);
            if ($buffer === false) {
                break;
            }

            $buffer .= $leftover;
            $lines = explode("\n", $buffer);

            // The first line is incomplete unless we reached start of file
            if ($position > 0) {
                $leftover = array_shift($lines);
            } else {
                $leftover = '';
            }

            // Loop lines in reverse since we read backwards
            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $line = trim($lines[$i]);
                if ($line === '') {
                    continue;
                }

                $totalScanned++;

                // Let's parse the line based on the log type
                $parsed = $this->parseLine($line, $type);

                if ($parsed !== null) {
                    // Laravel multi-line handler (stack trace accumulator)
                    if ($type === 'laravel' && ! empty($currentStack)) {
                        // The line matches a header, so we attach the stacked stack trace to the PREVIOUS log entry,
                        // which is actually the one we just read. But wait: since we are reading backwards,
                        // we would hit the stack trace lines first, then the header line.
                        // So any lines that failed parsing were collected in $currentStack (in reverse order).
                        // Now that we hit a header, we prepend the accumulated stack trace to this log message!
                        $stackMsg = implode("\n", array_reverse($currentStack));
                        $parsed['message'] .= "\n".$stackMsg;
                        $currentStack = [];
                    }

                    // Apply active filters
                    if ($this->matchesFilters($parsed, $filters)) {
                        if ($skipCount > 0) {
                            $skipCount--;
                        } else {
                            $logs[] = $parsed;
                            $matchedCount++;
                            if ($matchedCount >= $limit) {
                                // Check if there are more lines
                                $hasMore = ($position > 0 || $i > 0);
                                break 2;
                            }
                        }
                    }
                } else {
                    // This line could not be parsed. If we are parsing Laravel logs,
                    // it is highly likely a stack trace line. Store it for the next header.
                    if ($type === 'laravel') {
                        $currentStack[] = $line;
                    }
                }
            }
        }

        // If we hit the start of file and still have a lingering stack trace on the first line
        if ($type === 'laravel' && ! empty($currentStack) && count($logs) > 0) {
            // Attach it to the last processed log
            $lastIndex = count($logs) - 1;
            $stackMsg = implode("\n", array_reverse($currentStack));
            $logs[$lastIndex]['message'] .= "\n".$stackMsg;
        }

        fclose($handle);

        return [
            'logs' => $logs,
            'total_scanned' => $totalScanned,
            'has_more' => $hasMore,
        ];
    }

    /**
     * Parses a single log line into a normalized structure.
     *
     * @return array<string, mixed>|null
     */
    protected function parseLine(string $line, string $type): ?array
    {
        return match ($type) {
            'laravel' => $this->parseLaravelLine($line),
            'nginx_access', 'apache_access' => $this->parseAccessLine($line),
            'nginx_error', 'apache_error' => $this->parseErrorLine($line, $type),
            default => [
                'date' => null,
                'level' => 'info',
                'message' => $line,
                'raw' => $line,
            ],
        };
    }

    /**
     * Parse Laravel standard log format:
     * [2026-05-19 12:00:00] local.ERROR: RuntimeException: Something broke in /var/www...
     *
     * @return array<string, mixed>|null
     */
    protected function parseLaravelLine(string $line): ?array
    {
        $pattern = '/^\[(?<date>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (?<env>\w+)\.(?<level>\w+): (?<message>.*)/s';
        if (preg_match($pattern, $line, $matches)) {
            return [
                'date' => $matches['date'],
                'env' => $matches['env'],
                'level' => strtoupper($matches['level']),
                'message' => trim($matches['message']),
                'raw' => $line,
            ];
        }

        return null;
    }

    /**
     * Parse Nginx / Apache standard combined access logs:
     * 192.168.1.1 - - [19/May/2026:12:00:00 +0000] "GET /index.php HTTP/1.1" 200 4567 "https://referrer.com" "Mozilla/5.0..."
     *
     * @return array<string, mixed>|null
     */
    protected function parseAccessLine(string $line): ?array
    {
        $pattern = '/^(?<ip>\S+) \S+ \S+ \[(?<date>[^\]]+)\] "(?<method>\S+)\s+(?<url>\S+)\s+(?<protocol>[^"]+)" (?<status>\d{3}) (?<bytes>\S+)( "(?<referrer>[^"]*)")?( "(?<user_agent>[^"]*)")?/';

        if (preg_match($pattern, $line, $matches)) {
            $formattedDate = $this->parseCommonLogFormatDate($matches['date']);
            $status = (int) $matches['status'];

            // Standard level mapping based on status codes
            $level = 'INFO';
            if ($status >= 500) {
                $level = 'ERROR';
            } elseif ($status >= 400) {
                $level = 'WARNING';
            }

            return [
                'date' => $formattedDate,
                'ip' => $matches['ip'],
                'method' => $matches['method'],
                'url' => $matches['url'],
                'status' => $status,
                'bytes' => $matches['bytes'],
                'level' => $level,
                'message' => sprintf('%s %s - HTTP %d (%s)', $matches['method'], $matches['url'], $status, $matches['bytes']),
                'raw' => $line,
            ];
        }

        return null;
    }

    /**
     * Parse Nginx / Apache error logs.
     * Nginx: 2026/05/19 12:00:00 [error] 1234#0: *5678 client intended to send too large body...
     * Apache: [Tue May 19 12:00:00.123456 2026] [mpm_prefork:error] [pid 1234] [client 192.168.1.1:5678] AH00123: message...
     *
     * @return array<string, mixed>|null
     */
    protected function parseErrorLine(string $line, string $type): ?array
    {
        if (str_contains($type, 'nginx')) {
            $pattern = '/^(?<date>\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}) \[(?<level>[^\]]+)\] (?<message>.*)/';
            if (preg_match($pattern, $line, $matches)) {
                return [
                    'date' => str_replace('/', '-', $matches['date']),
                    'level' => strtoupper($matches['level']),
                    'message' => trim($matches['message']),
                    'raw' => $line,
                ];
            }
        } else {
            // Apache Error format
            $pattern = '/^\[[^\]]+ (?<date>\w{3} \d{2} \d{2}:\d{2}:\d{2}\.\d+ \d{4})\] \[[^:]+:(?<level>\w+)\] (\[pid \d+\] )?(\[client (?<ip>[^\]]+)\] )?(?<message>.*)/';
            if (preg_match($pattern, $line, $matches)) {
                $dateTime = DateTime::createFromFormat('M d H:i:s.u Y', $matches['date']);
                $dateStr = $dateTime ? $dateTime->format('Y-m-d H:i:s') : $matches['date'];

                return [
                    'date' => $dateStr,
                    'level' => strtoupper($matches['level']),
                    'ip' => $matches['ip'] ?? null,
                    'message' => trim($matches['message']),
                    'raw' => $line,
                ];
            }
        }

        return null;
    }

    /**
     * Verify if a parsed entry matches current request filters.
     *
     * @param  array<string, mixed>  $entry
     * @param  array<string, mixed>  $filters
     */
    protected function matchesFilters(array $entry, array $filters): bool
    {
        // 1. Log Level filter
        if (! empty($filters['level'])) {
            $level = strtoupper($filters['level']);
            if (strtoupper($entry['level'] ?? '') !== $level) {
                return false;
            }
        }

        // 2. IP filter
        if (! empty($filters['ip'])) {
            if (empty($entry['ip']) || ! str_contains($entry['ip'], $filters['ip'])) {
                return false;
            }
        }

        // 3. Status filter
        if (! empty($filters['status'])) {
            if (empty($entry['status']) || (int) $entry['status'] !== (int) $filters['status']) {
                return false;
            }
        }

        // 4. Search text filter (case-insensitive in message or IP or status)
        if (! empty($filters['search'])) {
            $query = strtolower($filters['search']);
            $msg = strtolower($entry['message'] ?? '');
            $raw = strtolower($entry['raw'] ?? '');
            if (! str_contains($msg, $query) && ! str_contains($raw, $query)) {
                return false;
            }
        }

        // 5. Date filters
        if (! empty($entry['date'])) {
            $entryTime = strtotime($entry['date']);
            if ($entryTime !== false) {
                if (! empty($filters['date_start'])) {
                    $startTime = strtotime($filters['date_start']);
                    if ($startTime !== false && $entryTime < $startTime) {
                        return false;
                    }
                }
                if (! empty($filters['date_end'])) {
                    $endTime = strtotime($filters['date_end']);
                    if ($endTime !== false && $entryTime > $endTime) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Format CLF Dates: 19/May/2026:12:00:00 +0000 -> 2026-05-19 12:00:00
     */
    protected function parseCommonLogFormatDate(string $clfDate): string
    {
        $parts = explode(' ', $clfDate);
        $dateTimeStr = $parts[0] ?? '';
        $dateTime = DateTime::createFromFormat('d/M/Y:H:i:s', $dateTimeStr);
        if ($dateTime) {
            return $dateTime->format('Y-m-d H:i:s');
        }

        return $clfDate;
    }
}
