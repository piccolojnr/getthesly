<?php

namespace App\Services\Research;

use InvalidArgumentException;

final readonly class ResearchInterpretation
{
    /** @param list<string> $concepts */
    public function __construct(
        public string $summary,
        public string $objective,
        public array $concepts,
        public ?string $clarification,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $keys = ['summary', 'objective', 'concepts', 'clarification'];

        if (array_diff(array_keys($data), $keys) !== [] || array_diff($keys, array_keys($data)) !== []) {
            throw new InvalidArgumentException('The research interpretation did not match its schema.');
        }

        $summary = self::validatedText($data['summary'], 500);
        $objective = self::validatedText($data['objective'], 500);
        $clarification = $data['clarification'] === null ? null : self::validatedText($data['clarification'], 500);

        if (! is_array($data['concepts']) || ! array_is_list($data['concepts']) || count($data['concepts']) > 8) {
            throw new InvalidArgumentException('The research concepts did not match their schema.');
        }

        $concepts = array_map(fn (mixed $concept): string => self::validatedText($concept, 100), $data['concepts']);

        return new self($summary, $objective, $concepts, $clarification);
    }

    /** @return array{summary: string, objective: string, concepts: list<string>, clarification: ?string} */
    public function toArray(): array
    {
        return [
            'summary' => $this->summary,
            'objective' => $this->objective,
            'concepts' => $this->concepts,
            'clarification' => $this->clarification,
        ];
    }

    private static function validatedText(mixed $value, int $maxLength): string
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException('The research interpretation contained an invalid field.');
        }

        $value = trim($value);

        if ($value === '' || mb_strlen($value) > $maxLength) {
            throw new InvalidArgumentException('The research interpretation contained an invalid field.');
        }

        return $value;
    }
}
