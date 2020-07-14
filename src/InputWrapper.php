<?php

namespace JamesClark32\DbTinker;

class InputWrapper
{
    protected ?string $connectionName = null;
    protected History $history;
    protected Query $query;

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    public function getUserInput(): Query
    {
        $this->query->setQueryText($this->fetchUserInput());
        $this->history->addQueryToHistoryIfShould($this->query->getQueryText());

        return $this->query;
    }

    public function setUserInput(string $userInput): void
    {
        $this->query->setQueryText($userInput);
    }

    public function setConnectionName(string $connectionName): self
    {
        $this->connectionName = $connectionName;
        return $this;
    }

    public function setHistory(History $history): self
    {
        $this->history = $history;
        return $this;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    protected function fetchUserInput(): string
    {
//        $data = readline($this->buildInputPrompt());
//        $array = str_split($data);
//        foreach($array as $bit) {
//            echo "\r ".$bit;
//            sleep(2);
//        }

        return readline($this->buildInputPrompt());
    }

    protected function buildInputPrompt(): string
    {
        return trans('db-tinker::input_prompts.input_query', [
            'connection' => $this->connectionName,
        ]);
    }
}