<?php

namespace Cognesy\Instructor\Utils;

class Profiler
{
    private array $checkpoints = [];
    static private Profiler $instance;

    static public function get() : static {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    static public function mark(string $name, array $context = []): void {
        self::get()->addMark($name, $context);
    }

    static public function summary() : void {
        self::get()->getSummary();
    }

    public function addMark(string $name, array $context = []) : void {
        $time = microtime(true);
        $previous = count($this->checkpoints) - 1;
        $delta = ($previous == -1) ? 0 : ($time - $this->checkpoints[$previous]['time']);
        $debugTrace = debug_backtrace()[1]['class'].'::'.debug_backtrace()[1]['function'];
        $this->store($name, $time, $delta, $debugTrace, $context);
    }

    public function getSummary() : void {
        $checkpoints = $this->checkpoints;
        $total = $this->getTotalTime();
        $output = "Total time: $total usec\n";
        foreach ($checkpoints as $checkpoint) {
            $delta = $checkpoint['delta'] * 1_000_000;
            // format $delta - remove fractional part
            $delta = number_format($delta, 2);
            // add spaces to align deltas
            $delta = str_pad($delta, 10, ' ', STR_PAD_LEFT);
            $context = $this->renderContext($checkpoint['context']);
            $output .= " $delta usec | {$checkpoint['name']}{$context} | {$checkpoint['debug']}\n";
        }
        print $output;
    }

    // INTERNAL /////////////////////////////////////////////////////////////////////

    private function getTotalTime() : float {
        $checkpoints = $this->checkpoints;
        return (end($checkpoints)['time'] - reset($checkpoints)['time']) * 1_000_000;
    }

    private function store(
        string $name,
        float $checkpoint,
        float $delta,
        string $debug,
        array $context
    ): void {
        $this->checkpoints[] = [
            'name' => $name,
            'time' => $checkpoint,
            'delta' => $delta,
            'debug' => $debug,
            'context' => $context,
        ];
    }

    private function renderContext(array $context) : string {
        if (empty($context)) {
            return '';
        }
        // turn key value pairs into a string, separated by commas
        $context = array_map(fn($key, $value) => "$key=$value", array_keys($context), $context);
        return '('.implode(', ', $context).')';
    }
}
