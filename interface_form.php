<?php
interface FormGeneratorInterface {
    public function __construct(string $action, string $method);
    public function addField(string $name, string $type, string $label, array $attributes = []): void;
    public function render(): void;
    public function handleSubmission(): bool;
    public static function getErrors(): array;
}

?>