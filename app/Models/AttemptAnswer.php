<?php

namespace App\Models;

class AttemptAnswer
{
    protected string $text;

    protected bool $is_correct;

    protected bool $was_selected;

    public function getIsCorrect(): bool
    {
        return $this->is_correct;
    }

    public function setIsCorrect(bool $is_correct): void
    {
        $this->is_correct = $is_correct;
    }

    public function getWasSelected(): bool
    {
        return $this->was_selected;
    }

    public function setWasSelected(bool $was_selected): void
    {
        $this->was_selected = $was_selected;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }
}
