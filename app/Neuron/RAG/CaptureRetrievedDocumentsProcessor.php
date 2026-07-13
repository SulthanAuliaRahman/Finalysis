<?php

declare(strict_types=1);

namespace App\Neuron\RAG;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\RAG\PostProcessor\PostProcessorInterface;

class CaptureRetrievedDocumentsProcessor implements PostProcessorInterface
{
    /** @param \Closure(array, Message): void $capture */
    public function __construct(private readonly \Closure $capture) {}

    public function process(Message $question, array $documents): array
    {
        ($this->capture)($documents, $question);

        return $documents;
    }
}
