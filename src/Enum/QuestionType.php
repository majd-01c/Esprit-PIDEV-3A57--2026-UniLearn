<?php

namespace App\Enum;

enum QuestionType: string
{
    case MCQ = 'MCQ';
    case MULTIPLE_CHOICE = 'MULTIPLE_CHOICE';
    case TRUE_FALSE = 'TRUE_FALSE';
    case TEXT = 'TEXT';

    public function canonical(): self
    {
        return $this === self::MULTIPLE_CHOICE ? self::MCQ : $this;
    }

    public function isChoiceBased(): bool
    {
        return in_array($this->canonical(), [self::MCQ, self::TRUE_FALSE], true);
    }

    public static function fromSubmitted(string $value): self
    {
        return self::from($value)->canonical();
    }

    /**
     * Values offered in forms. MULTIPLE_CHOICE is kept only for legacy data
     * created by the Java desktop app before both apps used MCQ.
     *
     * @return self[]
     */
    public static function formCases(): array
    {
        return [self::MCQ, self::TRUE_FALSE, self::TEXT];
    }
}
