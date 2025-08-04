<?php

namespace App\Models;

class GivenAnswer extends AttemptAnswer
{
    private int $id;

    private ?string $explanation;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation ?? null;
    }

    public function setExplanation(?string $explanation = null): void
    {
        $this->explanation = $explanation ?? null;
    }
}
