<?php

declare(strict_types=1);

namespace Empp\Model;

final readonly class Alert
{
    public function __construct(
        public AlertLevel $level,
        /** LEGACY, ALPHA, SLIP, KNF, THIN, EDGE, R1, MASS, SAT, INERT, RE, DARCY, BASIS */
        public string $code,
        public string $text,
    ) {
    }

    /** @return array{level: string, code: string, text: string} */
    public function toArray(): array
    {
        return ['level' => $this->level->value, 'code' => $this->code, 'text' => $this->text];
    }
}
