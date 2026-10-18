<?php

namespace App\Services\Research;

interface ResearchInterpreter
{
    public function interpret(string $title, string $question): ResearchInterpretation;
}
